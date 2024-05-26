import { Button, Dropdown, message, Progress, Space, Tree } from "antd";
import { useEffect, useState } from "react";
import { MoreOutlined, ExclamationCircleOutlined } from "@ant-design/icons";

import { useAppSelector } from "../../hooks";
import { mode as _mode } from "../../reducers/article-mode";
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
import { IDictRequest } from "../api/Dict";
import { useIntl } from "react-intl";
import { add } from "../../reducers/sent-word";
import store from "../../store";
import { settingInfo } from "../../reducers/setting";
import { GetUserSetting } from "../auth/setting/default";
import { getGrammar } from "../../reducers/term-vocabulary";
import modal from "antd/lib/modal";
import { UserWbwPost } from "../dict/MyCreate";
import { currentUser } from "../../reducers/current-user";
import Studio, { IStudio } from "../auth/Studio";
import { IChannel } from "../channel/Channel";
import TimeShow from "../general/TimeShow";
import moment from "moment";

export const getWbwProgress = (data: IWbw[]) => {
  //计算完成度
  //祛除标点符号
  const allWord = data.filter(
    (value) =>
      value.real.value &&
      value.real.value?.length > 0 &&
      value.type?.value !== ".ctl."
  );

  const final = allWord.filter(
    (value) =>
      value.meaning?.value &&
      value.factors?.value &&
      value.factorMeaning?.value &&
      value.case?.value &&
      value.parent?.value
  );
  console.debug("wbw progress", allWord, final);
  let finalLen: number = 0;
  final.forEach((value) => {
    if (value.real.value) {
      finalLen += value.real.value?.length;
    }
  });
  let allLen: number = 0;
  allWord.forEach((value) => {
    if (value.real.value) {
      allLen += value.real.value?.length;
    }
  });
  const progress = Math.round((finalLen * 100) / allLen);
  return progress;
};

export const paraMark = (wbwData: IWbw[]): IWbw[] => {
  //处理段落标记，支持点击段落引用弹窗
  let start = false;
  let bookCode = "";
  let count = 0;
  let bookCodeStack: string[] = [];
  wbwData.forEach((value: IWbw, index: number, array: IWbw[]) => {
    if (value.word.value === "(") {
      start = true;
      bookCode = "";
      bookCodeStack = [];
      return;
    }
    if (start) {
      if (!isNaN(Number(value.word.value.replaceAll("-", "")))) {
        console.debug("para mark", "number", value.word.value);

        if (bookCode === "" && bookCodeStack.length > 0) {
          //继承之前的
          let bookCodeList = bookCodeStack;
          bookCode = bookCodeList[0];
        }
        const dot = bookCode.lastIndexOf(".");
        let bookName = "";
        let paraNum = "";
        if (dot === -1) {
          bookName = bookCode;
          paraNum = value.word.value;
        } else {
          bookName = bookCode.substring(0, dot + 1);
          paraNum = bookCode.substring(dot + 1) + value.word.value;
        }
        bookName = bookName.substring(0, 64).toLowerCase();
        if (!bookCodeStack.includes(bookName)) {
          bookCodeStack.push(bookName);
        }
        if (bookName !== "") {
          array[index].bookName = bookName;
          count++;
        }
      } else if (value.word.value === ";") {
        bookCode = "";
        return;
      } else if (value.word.value === ")") {
        start = false;
        return;
      }
      bookCode += value.word.value;
    }
  });

  if (count > 0) {
    console.debug("para mark", count, wbwData);
  }
  return wbwData;
};

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
  data: { rows: IWbw[]; count: number };
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
  answer?: IWbw[];
  book: number;
  para: number;
  wordStart: number;
  wordEnd: number;
  channel?: IChannel;
  channelId: string;
  channelType?: TChannelType;
  channelLang?: string;
  display?: TWbwDisplayMode;
  fields?: IWbwFields;
  layoutDirection?: "h" | "v";
  refreshable?: boolean;
  mode?: ArticleMode;
  wbwProgress?: boolean;
  studio?: IStudio;
  readonly?: boolean;
  onMagicDictDone?: Function;
  onChange?: Function;
}
export const WbwSentCtl = ({
  data,
  answer,
  channel,
  channelId,
  channelType,
  channelLang,
  book,
  para,
  wordStart,
  wordEnd,
  display = "block",
  fields,
  layoutDirection = "h",
  mode,
  refreshable = false,
  wbwProgress = false,
  readonly = false,
  studio,
  onChange,
  onMagicDictDone,
}: IWidget) => {
  const intl = useIntl();
  const [wordData, setWordData] = useState<IWbw[]>(paraMark(data));
  const [wbwMode, setWbwMode] = useState(display);
  const [fieldDisplay, setFieldDisplay] = useState(fields);
  const [displayMode, setDisplayMode] = useState<ArticleMode>();
  const [loading, setLoading] = useState(false);

  const [showProgress, setShowProgress] = useState(false);
  const user = useAppSelector(currentUser);

  console.debug("wbw sent lang", channelLang);

  useEffect(() => setShowProgress(wbwProgress), [wbwProgress]);

  const settings = useAppSelector(settingInfo);
  const sysGrammar = useAppSelector(getGrammar)?.filter(
    (value) => value.tag === ":collocation:"
  );

  //计算完成度
  const progress = getWbwProgress(wordData);

  const newMode = useAppSelector(_mode);

  const update = (data: IWbw[]) => {
    console.debug("wbw update");
    setWordData(paraMark(data));
    if (typeof onChange !== "undefined") {
      onChange(data);
    }
  };

  useEffect(() => {
    if (refreshable) {
      setWordData(paraMark(data));
    }
  }, [data, refreshable]);

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
    console.log("mode", mode);
    let currMode: ArticleMode | undefined;
    if (typeof mode !== "undefined") {
      currMode = mode;
    } else if (typeof newMode !== "undefined") {
      if (typeof newMode.id === "undefined") {
        currMode = newMode.mode;
      } else {
        const sentId = newMode.id.split("-");
        if (sentId.length === 2) {
          if (book === parseInt(sentId[0]) && para === parseInt(sentId[1])) {
            currMode = newMode.mode;
          }
        }
      }
    } else {
      currMode = undefined;
    }
    setDisplayMode(currMode);
    switch (currMode) {
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
  }, [newMode, mode, book, para, display, fields]);

  const magicDictLookup = () => {
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
          if (channelType !== "nissaya") {
            saveWbwAll(json.data);
          }
        } else {
          console.error(json.message);
        }
      })
      .finally(() => {
        setLoading(false);
        if (typeof onMagicDictDone !== "undefined") {
          onMagicDictDone();
        }
      });
  };

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

  const saveWbwAll = (wbwData: IWbw[]) => {
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

    postWord(postParam);
  };
  const postWord = (postParam: IWbwRequest) => {
    const url = `/v2/wbw`;
    console.info("wbw api request", url, postParam);
    post<IWbwRequest, IWbwUpdateResponse>(url, postParam).then((json) => {
      console.info("wbw api response", json);
      if (json.ok) {
        message.info(json.data.count + " updated");
        setWordData(paraMark(json.data.rows));
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

      postWord(postParam);
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

  const wbwPublish = (wbwData: IWbw[], isPublic: boolean) => {
    let wordData: IDictRequest[] = [];

    wbwData.forEach((data) => {
      if (
        (typeof data.meaning?.value === "string" &&
          data.meaning?.value.trim().length > 0) ||
        (typeof data.factorMeaning?.value === "string" &&
          data.factorMeaning.value.trim().length > 0)
      ) {
        const [wordType, wordGrammar] = data.case?.value
          ? data.case?.value?.split("#")
          : ["", ""];
        let conf = data.confidence * 100;
        if (data.confidence.toString() === "0.5") {
          conf = 100;
        }
        wordData.push({
          word: data.real.value ? data.real.value : "",
          type: wordType,
          grammar: wordGrammar,
          mean: data.meaning?.value,
          parent: data.parent?.value,
          factors: data.factors?.value,
          factormean: data.factorMeaning?.value,
          note: data.note?.value,
          confidence: conf,
          language: channelLang,
          status: isPublic ? 30 : 5,
        });
      }
    });

    UserWbwPost(wordData, "wbw")
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

  interface wbwOptions {
    studio?: IStudio;
    answer?: IWbw;
  }
  const wbwRender = (item: IWbw, id: number, options?: wbwOptions) => {
    return (
      <WbwWord
        data={item}
        answer={options?.answer}
        channelId={channelId}
        key={id}
        mode={displayMode}
        display={wbwMode}
        fields={fieldDisplay}
        studio={studio}
        readonly={readonly}
        onChange={(e: IWbw, isPublish: boolean, isPublic: boolean) => {
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
          if (isPublish === true) {
            wbwPublish([e], isPublic);
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

  const resetWbw = () => {
    const newData: IWbw[] = [];
    let count = 0;
    wordData.forEach((value: IWbw) => {
      if (
        value.type?.value !== null &&
        value.type?.value !== ".ctl." &&
        value.real.value &&
        value.real.value.length > 0
      ) {
        count++;
        newData.push({
          uid: value.uid,
          book: value.book,
          para: value.para,
          sn: value.sn,
          word: value.word,
          real: value.real,
          style: value.style,
          meaning: { value: "", status: 7 },
          type: { value: "", status: 7 },
          grammar: { value: "", status: 7 },
          grammar2: { value: "", status: 7 },
          parent: { value: "", status: 7 },
          parent2: { value: "", status: 7 },
          case: { value: "", status: 7 },
          factors: { value: "", status: 7 },
          factorMeaning: { value: "", status: 7 },
          confidence: value.confidence,
        });
      } else {
        newData.push(value);
      }
    });
    message.info(`已经重置${count}个`);
    update(newData);
    saveWbwAll(newData);
  };

  let updatedAt = moment("1970-1-1");
  data.forEach((value) => {
    if (moment(value.updated_at).isAfter(updatedAt)) {
      updatedAt = moment(value.updated_at);
    }
  });

  return (
    <div style={{ width: "100%" }}>
      <div style={{ display: showProgress ? "flex" : "none" }}>
        <div className="progress" style={{ width: 400 }}>
          <Progress percent={progress} size="small" />
        </div>
        <Space>
          <Studio data={studio} hideAvatar />
          {<TimeShow updatedAt={updatedAt.toString()} />}
        </Space>
      </div>
      <div className={`layout-${layoutDirection}`}>
        <Dropdown
          menu={{
            items: [
              {
                key: "magic-dict-current",
                label: intl.formatMessage({
                  id: "buttons.magic-dict",
                }),
              },
              {
                key: "progress",
                label: "显示/隐藏进度条",
              },
              {
                key: "wbw-dict-publish-all",
                label: "发布全部单词",
              },
              {
                type: "divider",
              },
              {
                key: "copy-text",
                label: intl.formatMessage({
                  id: "buttons.copy.pali.text",
                }),
              },
              {
                key: "reset",
                label: intl.formatMessage({
                  id: "buttons.reset.wbw",
                }),
                danger: true,
              },
            ],
            onClick: ({ key }) => {
              console.log(`Click on item ${key}`);
              switch (key) {
                case "magic-dict-current":
                  setLoading(true);
                  magicDictLookup();
                  break;
                case "wbw-dict-publish-all":
                  wbwPublish(
                    wordData,
                    user?.roles?.includes("basic") ? false : true
                  );
                  break;
                case "copy-text":
                  const paliText = wordData
                    .filter((value) => value.type?.value !== ".ctl.")
                    .map((item) => item.word.value)
                    .join(" ");
                  navigator.clipboard.writeText(paliText).then(() => {
                    message.success("已经拷贝到剪贴板");
                  });
                  break;
                case "progress":
                  setShowProgress((origin) => !origin);
                  break;
                case "reset":
                  modal.confirm({
                    title: "清除逐词解析数据",
                    icon: <ExclamationCircleOutlined />,
                    content: "清除这个句子的逐词解析数据，此操作不可恢复",
                    okText: "确认",
                    cancelText: "取消",
                    onOk: () => {
                      resetWbw();
                    },
                  });
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
          wordData
            .map((item, index) => {
              let newItem = item;
              const spell = item.real.value;
              if (spell) {
                const matched = sysGrammar?.find((value) =>
                  value.word.split("...").includes(spell)
                );
                if (matched) {
                  console.debug("wbw sent grammar matched", matched);
                  newItem.grammarId = matched.guid;
                }
              }
              return newItem;
            })
            .map((item, id) => {
              const currAnswer = answer?.find(
                (value) => value.sn.join() === item.sn.join()
              );
              return wbwRender(item, id, {
                studio: studio,
                answer: currAnswer,
              });
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
