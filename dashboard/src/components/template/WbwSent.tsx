import { message } from "antd";
import { useEffect, useState } from "react";

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
interface IWbwRequest {
  book: number;
  para: number;
  sn: number;
  channel_id: string;
  data: IWbwXml[];
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
    if (typeof magicDict === "undefined") {
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
        } else {
          console.error(json.message);
        }
      })
      .finally(() => {
        if (typeof onMagicDictDone !== "undefined") {
          onMagicDictDone();
        }
      });
  }, [magicDict]);

  return (
    <div style={{ display: "flex", flexWrap: "wrap" }}>
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
                if (value.sn.join() === e.sn.join()) {
                  console.log("found", e.sn);
                  array[index] = e;
                }
              });

              const data = newData.filter((value) => value.sn[0] === e.sn[0]);
              const postParam: IWbwRequest = {
                book: book,
                para: para,
                sn: e.sn[0],
                channel_id: channelId,
                data: data.map((item) => {
                  return {
                    pali: item.word,
                    real: item.real,
                    id: `${book}-${para}-` + e.sn.join("-"),
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
                }),
              };

              post<IWbwRequest, IWbwUpdateResponse>(`/v2/wbw`, postParam).then(
                (json) => {
                  if (json.ok) {
                    message.info(e.word.value + " updated");
                  } else {
                    message.error(json.message);
                  }
                }
              );

              update(newData);
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
              } else {
                //拆开
                console.log("拆开");
                let factors = wordData[id]?.factors?.value;
                if (typeof factors === "string") {
                  if (wordData[id].case?.value?.split("#")[0] === ".un.") {
                    factors = `[+${factors}+]`;
                  } else {
                    factors = factors.replaceAll("+", "+-+");
                  }

                  const children: IWbw[] | undefined = factors
                    .split("+")
                    .map((item, index) => {
                      return {
                        word: { value: item, status: 5 },
                        real: { value: PaliReal(item), status: 5 },
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
