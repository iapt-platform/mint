import { Button, Dropdown, message, Tree } from "antd";
import { useEffect, useState } from "react";
import { MoreOutlined } from "@ant-design/icons";

import { useAppSelector } from "../../hooks";
import { mode } from "../../reducers/article-mode";
import { post } from "../../request";
import { ArticleMode } from "../article/Article";
import WbwWord, {
  IWbw,
  IWbwFields,
  TWbwDisplayMode,
  WbwElement,
  WbwStatus,
} from "./Wbw/WbwWord";
import { TChannelType } from "../api/Channel";
import { IDictRequest, IDictResponse, IUserDictCreate } from "../api/Dict";
import { useIntl } from "react-intl";
import { add } from "../../reducers/sent-word";
import store from "../../store";
import { settingInfo } from "../../reducers/setting";
import { GetUserSetting } from "../auth/setting/default";

interface IMagicDictRequest {
  book: number;
  para: number;
  word_start: number;
  word_end: number;
  data: IWbw[];
  channel_id: string;
  lang?: string[];
}
interface IMagicDictResponse {
  ok: boolean;
  message: string;
  data: IWbw[];
}

interface IWbwXml {
  id: string;
  pali: WbwElement<string>;
  real?: WbwElement<string | null>;
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
  channelType?: TChannelType;
  display?: TWbwDisplayMode;
  fields?: IWbwFields;
  layoutDirection?: "h" | "v";
  magicDict?: string;
  refreshable?: boolean;
  onMagicDictDone?: Function;
  onChange?: Function;
}
export const WbwSentCtl = ({
  data,
  channelId,
  channelType,
  book,
  para,
  wordStart,
  wordEnd,
  display = "block",
  fields,
  layoutDirection = "h",
  magicDict,
  refreshable = false,
  onChange,
  onMagicDictDone,
}: IWidget) => {
  const intl = useIntl();
  const [wordData, setWordData] = useState<IWbw[]>(data);
  const [wbwMode, setWbwMode] = useState(display);
  const [fieldDisplay, setFieldDisplay] = useState(fields);
  const [displayMode, setDisplayMode] = useState<ArticleMode>();
  const [magic, setMagic] = useState<string>();
  const [loading, setLoading] = useState(false);
  const settings = useAppSelector(settingInfo);

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
    //发布句子里面的单词的变更。给术语输入菜单用。
    let words = new Map<string, number>();
    wordData
      .filter(
        (value) =>
          value.type?.value !== null &&
          value.type?.value !== ".ctl." &&
          value.real.value &&
          value.real.value.length > 0
      )
      .forEach((value) => {
        words.set(value.real.value ? value.real.value : "", 1);
        if (value.parent?.value) {
          words.set(value.parent.value, 1);
        }
      });
    let pubWords: string[] = [];
    words.forEach((value, key) => pubWords.push(key));

    const sentId = `${book}-${para}-${wordStart}-${wordEnd}`;
    store.dispatch(add({ sentId: sentId, words: pubWords }));
  }, [book, para, wordData, wordEnd, wordStart]);

  useEffect(() => {
    setDisplayMode(newMode);
    switch (newMode) {
      case "edit":
        if (typeof display === "undefined") {
          setWbwMode("block");
        }

        if (typeof fields === "undefined") {
          setFieldDisplay({
            meaning: true,
            factors: false,
            factorMeaning: false,
            case: false,
          });
        }

        break;
      case "wbw":
        if (typeof display === "undefined") {
          setWbwMode("block");
        }
        if (typeof fields === "undefined") {
          setFieldDisplay({
            meaning: true,
            factors: true,
            factorMeaning: true,
            case: true,
          });
        }
        break;
    }
  }, [newMode]);

  useEffect(() => {
    if (typeof magic === "undefined") {
      return;
    }
    let _lang = GetUserSetting("setting.dict.lang", settings);
    const url = `/v2/wbwlookup`;
    console.log("magic dict url", url);
    if (typeof _lang === "object") {
    }
    post<IMagicDictRequest, IMagicDictResponse>(url, {
      book: book,
      para: para,
      word_start: wordStart,
      word_end: wordEnd,
      data: wordData,
      channel_id: channelId,
      lang: _lang?.toString().split(","),
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
      attachments: JSON.stringify(item.attachments),
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
    if (channelType === "nissaya") {
    } else {
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

      post<IWbwRequest, IWbwUpdateResponse>(`/v2/wbw`, postParam).then(
        (json) => {
          if (json.ok) {
            message.info(json.data.count + " updated");
          } else {
            message.error(json.message);
          }
        }
      );
    }
  };

  const wordSplit = (id: number, hyphen = "-") => {
    let factors = wordData[id]?.factors?.value;
    if (typeof factors === "string") {
      let sFm = wordData[id]?.factorMeaning?.value;
      if (typeof sFm === "undefined" || sFm === null) {
        sFm = new Array(factors.split("+")).fill([""]).join("+");
      }
      if (wordData[id].case?.value?.split("#")[0] === ".un.") {
        factors = `[+${factors}+]`;
        sFm = `+${sFm}+`;
      } else if (hyphen !== "") {
        factors = factors.replaceAll("+", `+${hyphen}+`);
        sFm = sFm.replaceAll("+", `+${hyphen}+`);
      }
      let fm = sFm.split("+");

      const children: IWbw[] | undefined = factors
        .split("+")
        .map((item, index) => {
          return {
            word: { value: item, status: 5 },
            real: {
              value: item
                .replaceAll("-", "")
                .replaceAll("[", "")
                .replaceAll("]", ""),
              status: 5,
            },
            meaning: { value: fm[index], status: 5 },
            book: wordData[id].book,
            para: wordData[id].para,
            sn: [...wordData[id].sn, index],
            confidence: 1,
          };
        });
      if (typeof children !== "undefined") {
        console.log("children", children);
        const newData: IWbw[] = [...wordData];
        newData.splice(id + 1, 0, ...children);
        console.log("new-data", newData);
        update(newData);
        saveWord(newData, wordData[id].sn[0]);
      }
    }
  };

  const wbwPublish = (wbwData: IWbw[]) => {
    let wordData: IDictRequest[] = [];

    wbwData.forEach((data) => {
      const [wordType, wordGrammar] = data.case?.value
        ? data.case?.value?.split("#")
        : ["", ""];

      wordData.push({
        word: data.real.value ? data.real.value : "",
        type: wordType,
        grammar: wordGrammar,
        mean: data.meaning?.value,
        parent: data.parent?.value,
        factors: data.factors?.value,
        factormean: data.factorMeaning?.value,
        confidence: data.confidence,
      });
      if (data.parent?.value && wordType !== "") {
        if (!wordType.includes("base") && wordType !== ".ind.") {
          wordData.push({
            word: data.parent.value,
            type: "." + wordType.replaceAll(".", "") + ":base.",
            grammar: wordGrammar,
            mean: data.meaning?.value,
            parent: data.parent2?.value ? data.parent2?.value : undefined,
            factors: data.factors?.value,
            factormean: data.factorMeaning?.value,
            confidence: data.confidence,
          });
        }
      }
    });

    post<IUserDictCreate, IDictResponse>("/v2/userdict", {
      view: "wbw",
      data: JSON.stringify(wordData),
    })
      .finally(() => {
        setLoading(false);
      })
      .then((json) => {
        if (json.ok) {
          message.success(
            "wbw " + intl.formatMessage({ id: "flashes.success" })
          );
        } else {
          message.error(json.message);
        }
      });
  };
  const wbwRender = (item: IWbw, id: number) => {
    return (
      <WbwWord
        data={item}
        key={id}
        mode={displayMode}
        display={wbwMode}
        fields={fieldDisplay}
        onChange={(e: IWbw, isPublish?: boolean) => {
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
                  value.real.value &&
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
              if (value.sn.join() === e.sn.slice(0, e.sn.length - 1).join()) {
                console.log("found", value.sn);
                array[index].factorMeaning = {
                  value: factorMeaning,
                  status: 5,
                };
                if (array[index].meaning?.status !== WbwStatus.manual) {
                  array[index].meaning = {
                    value: factorMeaning.replaceAll("+", " "),
                    status: 5,
                  };
                }
              }
            });
          }
          update(newData);
          saveWord(newData, e.sn[0]);
          if (isPublish) {
            wbwPublish([e]);
          }
        }}
        onSplit={() => {
          const newData: IWbw[] = JSON.parse(JSON.stringify(wordData));

          if (
            id < wordData.length - 1 &&
            wordData[id + 1].sn.join("-").indexOf(wordData[id].sn.join("-")) ===
              0
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
            wordSplit(id);
          }
        }}
      />
    );
  };

  return (
    <div className={`layout-${layoutDirection}`}>
      <Dropdown
        menu={{
          items: [
            {
              key: "magic-dict-current",
              label: "神奇字典",
            },
            {
              key: "wbw-dict-publish-all",
              label: "发布全部单词",
            },
          ],
          onClick: ({ key }) => {
            console.log(`Click on item ${key}`);
            switch (key) {
              case "magic-dict-current":
                setLoading(true);
                setMagic("curr");
                break;
              case "wbw-dict-publish-all":
                wbwPublish(wordData);
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
      {layoutDirection === "h" ? (
        wordData.map((item, id) => {
          return wbwRender(item, id);
        })
      ) : (
        <Tree
          selectable={true}
          blockNode
          treeData={wordData
            .filter((value) => value.sn.length === 1)
            .map((item, id) => {
              const children = wordData.filter(
                (value) =>
                  value.sn.length === 2 &&
                  value.sn.slice(0, 1).join() === wordData[id].sn.join()
              );

              return {
                title: wbwRender(item, id),
                key: item.sn.join(),
                isLeaf: !item.factors?.value?.includes("+"),
                children:
                  children.length > 0
                    ? children.map((item, id) => {
                        return {
                          title: wbwRender(item, id),
                          key: item.sn.join(),
                          isLeaf: true,
                        };
                      })
                    : undefined,
              };
            })}
          loadData={({ key, children }: any) =>
            new Promise<void>((resolve) => {
              console.log("key", key, children);
              if (children) {
                resolve();
                return;
              }

              wordSplit(key, "");
              resolve();
            })
          }
        />
      )}
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
