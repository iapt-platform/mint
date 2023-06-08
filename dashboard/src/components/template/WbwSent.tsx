import { Button, Dropdown, message, Popover } from "antd";
import { useEffect, useState } from "react";
import { MoreOutlined } from "@ant-design/icons";

import { useAppSelector } from "../../hooks";
import { mode } from "../../reducers/article-mode";
import { post } from "../../request";
import { PaliReal } from "../../utils";
import { ArticleMode } from "../article/Article";
import WbwWord, { IWbw, IWbwFields, WbwElement } from "./Wbw/WbwWord";

interface IMagicDictRequest {
  book: number;
  para: number;
  word_start: number;
  word_end: number;
  data: IWbw[];
  channel_id: string;
}
interface IMagicDictResponse {
  ok: boolean;
  message: string;
  data: IWbw[];
}

interface IWbwXml {
  id: string;
  pali: WbwElement<string>;
  real?: WbwElement<string>;
  type?: WbwElement<string | null>;
  gramma?: WbwElement<string | null>;
  mean?: WbwElement<string | null>;
  org?: WbwElement<string | null>;
  om?: WbwElement<string | null>;
  case?: WbwElement<string | null>;
  parent?: WbwElement<string | null>;
  pg?: WbwElement<string | null>;
  parent2?: WbwElement<string | null>;
  rela?: WbwElement<string | null>;
  lock?: boolean;
  bmt?: WbwElement<string | null>;
  bmc?: WbwElement<number | null>;
  cf: number;
}
interface IWbwUpdateResponse {
  ok: boolean;
  message: string;
  data: { rows?: IWbwXml[]; count: number };
}
interface IWbwWord {
  words: IWbwXml[];
  sn: number;
}
interface IWbwRequest {
  book: number;
  para: number;
  sn: number;
  channel_id: string;
  data: IWbwWord[];
}
interface IWidget {
  data: IWbw[];
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  channelId: string;
  display?: "block" | "inline";
  fields?: IWbwFields;
  magicDict?: string;
  refreshable?: boolean;
  onMagicDictDone?: Function;
  onChange?: Function;
}
export const WbwSentCtl = ({
  data,
  channelId,
  book,
  para,
  wordStart,
  wordEnd,
  display = "inline",
  fields,
  magicDict,
  refreshable = false,
  onChange,
  onMagicDictDone,
}: IWidget) => {
  const [wordData, setWordData] = useState<IWbw[]>(data);
  const [wbwMode, setWbwMode] = useState(display);
  const [fieldDisplay, setFieldDisplay] = useState(fields);
  const [displayMode, setDisplayMode] = useState<ArticleMode>();
  const [magic, setMagic] = useState<string>();
  const [loading, setLoading] = useState(false);
  useEffect(() => {
    setMagic(magicDict);
  }, [magicDict]);

  const newMode = useAppSelector(mode);
  const update = (data: IWbw[]) => {
    setWordData(data);
    if (typeof onChange !== "undefined") {
      onChange(data);
    }
  };
  useEffect(() => {
    if (refreshable) {
      setWordData(data);
    }
  }, [data]);

  useEffect(() => {
    setDisplayMode(newMode);
    switch (newMode) {
      case "edit":
        setWbwMode("block");

        setFieldDisplay({
          meaning: true,
          factors: false,
          factorMeaning: false,
          case: false,
        });
        break;
      case "wbw":
        setWbwMode("block");
        setFieldDisplay({
          meaning: true,
          factors: true,
          factorMeaning: true,
          case: true,
        });
        break;
    }
  }, [newMode]);

  useEffect(() => {
    if (typeof magic === "undefined") {
      return;
    }
    const url = `/v2/wbwlookup`;
    console.log("magic dict url", url);
    post<IMagicDictRequest, IMagicDictResponse>(url, {
      book: book,
      para: para,
      word_start: wordStart,
      word_end: wordEnd,
      data: wordData,
      channel_id: channelId,
    })
      .then((json) => {
        if (json.ok) {
          console.log("magic dict result", json.data);
          update(json.data);
          updateWbwAll(json.data);
        } else {
          console.error(json.message);
        }
      })
      .finally(() => {
        setMagic(undefined);
        setLoading(false);
        if (typeof onMagicDictDone !== "undefined") {
          onMagicDictDone();
        }
      });
  }, [magic]);

  const wbwToXml = (item: IWbw) => {
    return {
      pali: item.word,
      real: item.real,
      id: `${book}-${para}-` + item.sn.join("-"),
      type: item.type,
      gramma: item.grammar,
      mean: item.meaning
        ? {
            value: item.meaning.value,
            status: item.meaning?.status,
          }
        : undefined,
      org: item.factors,
      om: item.factorMeaning,
      case: item.case,
      parent: item.parent,
      pg: item.grammar2,
      parent2: item.parent2,
      rela: item.relation,
      lock: item.locked,
      note: item.note,
      bmt: item.bookMarkText,
      bmc: item.bookMarkColor,
      cf: item.confidence,
    };
  };

  const updateWbwAll = (wbwData: IWbw[]) => {
    let arrSn: number[] = [];
    wbwData.forEach((value) => {
      if (!arrSn.includes(value.sn[0])) {
        arrSn.push(value.sn[0]);
      }
    });

    const postParam: IWbwRequest = {
      book: book,
      para: para,
      channel_id: channelId,
      sn: wbwData[0].sn[0],
      data: arrSn.map((item) => {
        return {
          sn: item,
          words: wbwData.filter((value) => value.sn[0] === item).map(wbwToXml),
        };
      }),
    };

    post<IWbwRequest, IWbwUpdateResponse>(`/v2/wbw`, postParam).then((json) => {
      if (json.ok) {
        message.info(json.data.count + " updated");
      } else {
        message.error(json.message);
      }
    });
  };

  const saveWord = (wbwData: IWbw[], sn: number) => {
    const data = wbwData.filter((value) => value.sn[0] === sn);

    const postParam: IWbwRequest = {
      book: book,
      para: para,
      channel_id: channelId,
      sn: sn,
      data: [
        {
          sn: sn,
          words: data.map(wbwToXml),
        },
      ],
    };

    post<IWbwRequest, IWbwUpdateResponse>(`/v2/wbw`, postParam).then((json) => {
      if (json.ok) {
        message.info(json.data.count + " updated");
      } else {
        message.error(json.message);
      }
    });
  };

  return (
    <div style={{ display: "flex", flexWrap: "wrap" }}>
      <Dropdown
        menu={{
          items: [
            {
              key: "magic-dict-current",
              label: "魔法字典",
            },
          ],
          onClick: ({ key }) => {
            console.log(`Click on item ${key}`);
            switch (key) {
              case "magic-dict-current":
                setLoading(true);
                setMagic("curr");
                break;
            }
          },
        }}
        placement="bottomLeft"
      >
        <Button
          loading={loading}
          onClick={(e) => e.preventDefault()}
          icon={<MoreOutlined />}
          size="small"
          type="text"
          style={{ backgroundColor: "lightblue", opacity: 0.3 }}
        />
      </Dropdown>
      {wordData.map((item, id) => {
        return (
          <WbwWord
            data={item}
            key={id}
            mode={displayMode}
            display={wbwMode}
            fields={fieldDisplay}
            onChange={(e: IWbw) => {
              console.log("word changed", e);

              let newData = [...wordData];
              newData.forEach((value, index, array) => {
                //把新的数据更新到数组
                if (value.sn.join() === e.sn.join()) {
                  console.log("found", e.sn);
                  array[index] = e;
                }
              });

              if (e.sn.length > 1) {
                //把meaning 数据更新到 拆分前单词的factor meaning
                const factorMeaning = newData
                  .filter((value) => {
                    if (
                      value.sn.length === e.sn.length &&
                      e.sn.slice(0, e.sn.length - 1).join() ===
                        value.sn.slice(0, e.sn.length - 1).join() &&
                      value.real.value.length > 0
                    ) {
                      return true;
                    } else {
                      return false;
                    }
                  })
                  .map((item) => item.meaning?.value)
                  .join("+");
                console.log("fm", factorMeaning);
                newData.forEach((value, index, array) => {
                  //把新的数据更新到数组
                  if (
                    value.sn.join() === e.sn.slice(0, e.sn.length - 1).join()
                  ) {
                    console.log("found", value.sn);
                    array[index].factorMeaning = {
                      value: factorMeaning,
                      status: 5,
                    };
                  }
                });
              }
              update(newData);
              saveWord(newData, e.sn[0]);
            }}
            onSplit={() => {
              const newData: IWbw[] = JSON.parse(JSON.stringify(wordData));

              if (
                id < wordData.length - 1 &&
                wordData[id + 1].sn
                  .join("-")
                  .indexOf(wordData[id].sn.join("-")) === 0
              ) {
                //合并
                console.log("合并");
                const compactData = newData.filter((value, index) => {
                  if (
                    index !== id &&
                    value.sn.join("-").indexOf(wordData[id].sn.join("-")) === 0
                  ) {
                    return false;
                  } else {
                    return true;
                  }
                });
                update(compactData);
                saveWord(compactData, wordData[id].sn[0]);
              } else {
                //拆开
                console.log("拆开");
                let factors = wordData[id]?.factors?.value;
                if (typeof factors === "string") {
                  let sFm = wordData[id]?.factorMeaning?.value;
                  if (typeof sFm === "undefined" || sFm === null) {
                    sFm = new Array(factors.split("+")).fill([""]).join("+");
                  }
                  if (wordData[id].case?.value?.split("#")[0] === ".un.") {
                    factors = `[+${factors}+]`;
                    sFm = `+${sFm}+`;
                  } else {
                    factors = factors.replaceAll("+", "+-+");
                    sFm = sFm.replaceAll("+", "+-+");
                  }
                  let fm = sFm.split("+");

                  const children: IWbw[] | undefined = factors
                    .split("+")
                    .map((item, index) => {
                      return {
                        word: { value: item, status: 5 },
                        real: { value: PaliReal(item), status: 5 },
                        meaning: { value: fm[index], status: 5 },
                        book: wordData[id].book,
                        para: wordData[id].para,
                        sn: [...wordData[id].sn, index],
                        confidence: 1,
                      };
                    });
                  if (typeof children !== "undefined") {
                    console.log("children", children);
                    newData.splice(id + 1, 0, ...children);
                    console.log("new-data", newData);
                    update(newData);
                    saveWord(newData, wordData[id].sn[0]);
                  }
                }
              }
            }}
          />
        );
      })}
    </div>
  );
};

interface IWidgetWbwSent {
  props: string;
}
const WbwSentWidget = ({ props }: IWidgetWbwSent) => {
  const prop = JSON.parse(atob(props)) as IWidget;
  return <WbwSentCtl {...prop} />;
};

export default WbwSentWidget;
