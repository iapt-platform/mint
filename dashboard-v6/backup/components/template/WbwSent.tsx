import { Alert, Button, Dropdown, message, Progress, Space, Tree } from "antd";
import { useEffect, useState, useMemo, useCallback, memo } from "react";
import { MoreOutlined, ExclamationCircleOutlined } from "@ant-design/icons";

import { useAppSelector } from "../../hooks";
import { mode as _mode } from "../../reducers/article-mode";
import { delete_, get, post } from "../../request";
import type { ArticleMode } from "../article/Article";
import WbwWord, {
  type IWbw,
  type IWbwFields,
  type TWbwDisplayMode,
  type WbwElement,
  WbwStatus,
} from "./Wbw/WbwWord";
import type { TChannelType } from "../../api/Channel";
import type { IDictRequest } from "../../api/Dict";
import { useIntl } from "react-intl";
import { add } from "../../reducers/sent-word";
import store from "../../store";
import { settingInfo } from "../../reducers/setting";
import { GetUserSetting } from "../auth/setting/default";
import { getGrammar } from "../../reducers/term-vocabulary";
import modal from "antd/lib/modal";
import { UserWbwPost } from "../dict/MyCreate";
import { currentUser } from "../../reducers/current-user";
import Studio, { type IStudio } from "../auth/Studio";
import type { IChannel } from "../channel/Channel";
import TimeShow from "../general/TimeShow";
import moment from "moment";
import { courseInfo } from "../../reducers/current-course";
import type { ISentenceWbwListResponse } from "../../api/Corpus";
import type { IDeleteResponse } from "../../api/Article";
import { useWbwStreamProcessor } from "./AIWbw";
import { siteInfo } from "../../reducers/layout";

// ============ 优化工具函数 ============

// 优化1: 使用 Map 缓存 sn 索引,提升查找性能从 O(n) 到 O(1)
const createSnIndexMap = (data: IWbw[]): Map<string, IWbw> => {
  const map = new Map<string, IWbw>();
  data.forEach((item) => {
    map.set(item.sn.join(), item);
  });
  return map;
};

// 优化2: 缓存字符串拼接结果
const createSnKey = (sn: number[]): string => sn.join();

// 优化3: 提取 paraMark 为纯函数,便于 memoization
export const paraMark = (wbwData: IWbw[]): IWbw[] => {
  if (!wbwData || wbwData.length === 0) return wbwData;

  let start = false;
  let bookCode = "";
  let count = 0;
  let bookCodeStack: string[] = [];

  // 使用浅拷贝而非深拷贝
  const result = [...wbwData];

  result.forEach((value: IWbw, index: number) => {
    if (value.word.value === "(") {
      start = true;
      bookCode = "";
      bookCodeStack = [];
      return;
    }
    if (start) {
      if (!isNaN(Number(value.word.value.replaceAll("-", "")))) {
        if (bookCode === "" && bookCodeStack.length > 0) {
          bookCode = bookCodeStack[0];
        }
        const dot = bookCode.lastIndexOf(".");
        let bookName = "";
        if (dot === -1) {
          bookName = bookCode;
        } else {
          bookName = bookCode.substring(0, dot + 1);
        }
        bookName = bookName.substring(0, 64).toLowerCase();
        if (!bookCodeStack.includes(bookName)) {
          bookCodeStack.push(bookName);
        }
        if (bookName !== "") {
          result[index] = { ...result[index], bookName };
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
    console.debug("para mark", count);
  }
  return result;
};

// 优化4: 提取进度计算为纯函数
export const getWbwProgress = (data: IWbw[], answer?: IWbw[]): number => {
  const allWord = data.filter(
    (value) =>
      value.real.value &&
      value.real.value?.length > 0 &&
      value.type?.value !== ".ctl."
  );

  if (allWord.length === 0) return 0;

  let final: IWbw[];
  if (answer) {
    // 使用 Map 优化查找
    const answerMap = createSnIndexMap(answer);

    final = allWord.filter((value: IWbw) => {
      const snKey = createSnKey(value.sn);
      const currAnswer = answerMap.get(snKey);

      if (!currAnswer) return false;

      const checks = [
        ["meaning", currAnswer.meaning?.value, value.meaning?.value],
        ["factors", currAnswer.factors?.value, value.factors?.value],
        [
          "factorMeaning",
          currAnswer.factorMeaning?.value,
          value.factorMeaning?.value,
        ],
        ["case", currAnswer.case?.value, value.case?.value],
        ["parent", currAnswer.parent?.value, value.parent?.value],
      ];

      return checks.every(([_, answerVal, valueVal]) => {
        if (!answerVal) return true;
        return valueVal && valueVal.trim().length > 0;
      });
    });
  } else {
    final = allWord.filter(
      (value) =>
        value.meaning?.value &&
        value.factors?.value &&
        value.factorMeaning?.value &&
        value.case?.value
    );
  }

  const finalLen = final.reduce(
    (sum, v) => sum + (v.real.value?.length || 0),
    0
  );
  const allLen = allWord.reduce(
    (sum, v) => sum + (v.real.value?.length || 0),
    0
  );

  return allLen > 0 ? Math.round((finalLen * 100) / allLen) : 0;
};

// ============ 接口定义 ============

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

// ============ 主组件 ============

export const WbwSentCtl = memo(
  ({
    data,
    answer,
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

    // ============ State ============
    const [wordData, setWordData] = useState<IWbw[]>(() => paraMark(data));
    const [wbwMode, setWbwMode] = useState(display);
    const [fieldDisplay, setFieldDisplay] = useState(fields);
    const [displayMode, setDisplayMode] = useState<ArticleMode>();
    const [loading, setLoading] = useState(false);
    const [showProgress, setShowProgress] = useState(false);
    const [check, setCheck] = useState(answer ? true : false);
    const [courseAnswer, setCourseAnswer] = useState<IWbw[]>();

    const { processStream, isProcessing, wbwData, error } =
      useWbwStreamProcessor();

    // ============ Selectors ============
    const user = useAppSelector(currentUser);
    const course = useAppSelector(courseInfo);
    const site = useAppSelector(siteInfo);
    const settings = useAppSelector(settingInfo);
    const newMode = useAppSelector(_mode);
    const sysGrammar = useAppSelector(getGrammar)?.filter(
      (value) => value.tag === ":collocation:"
    );

    // ============ Memoized Values ============

    // 优化5: 缓存句子ID
    const sentId = useMemo(
      () => `${book}-${para}-${wordStart}-${wordEnd}`,
      [book, para, wordStart, wordEnd]
    );

    // 优化6: 缓存模型配置
    const wbwModel = useMemo(
      () => site?.settings?.models?.wbw?.[0] ?? null,
      [site?.settings?.models?.wbw]
    );

    // 优化7: 缓存进度计算
    const progress = useMemo(
      () => getWbwProgress(wordData, answer),
      [wordData, answer]
    );

    // 优化8: 缓存更新时间
    const updatedAt = useMemo(() => {
      let latest = moment("1970-1-1");
      data.forEach((value) => {
        if (moment(value.updated_at).isAfter(latest)) {
          latest = moment(value.updated_at);
        }
      });
      return latest;
    }, [data]);

    // 优化9: 使用 Map 缓存语法匹配
    const grammarMap = useMemo(() => {
      if (!sysGrammar) return new Map();
      const map = new Map<string, string>();
      sysGrammar.forEach((g) => {
        g.word.split("...").forEach((word) => {
          map.set(word, g.guid ?? "1");
        });
      });
      return map;
    }, [sysGrammar]);

    // ============ Callbacks ============

    // 优化10: 使用 useCallback 缓存回调函数
    const update = useCallback(
      (data: IWbw[], replace: boolean = true) => {
        if (replace) {
          setWordData(paraMark(data));
        } else {
          setWordData((origin) => {
            const dataMap = createSnIndexMap(data);
            return origin.map((value) => {
              const snKey = createSnKey(value.sn);
              const newOne = dataMap.get(snKey);
              if (newOne) return newOne;

              // 检查 real.value 匹配
              const byReal = data.find(
                (d) => d.real.value === value.real.value
              );
              return byReal || value;
            });
          });
        }

        if (typeof onChange !== "undefined") {
          onChange(data);
        }
      },
      [onChange]
    );

    const wbwToXml = useCallback(
      (item: IWbw) => {
        return {
          pali: item.word,
          real: item.real,
          id: `${book}-${para}-${createSnKey(item.sn).replace(/,/g, "-")}`,
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
      },
      [book, para]
    );

    const postWord = useCallback((postParam: IWbwRequest) => {
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
    }, []);

    const saveWbwAll = useCallback(
      (wbwData: IWbw[]) => {
        const snSet = new Set<number>();
        wbwData.forEach((value) => {
          snSet.add(value.sn[0]);
        });
        const arrSn = Array.from(snSet);

        const postParam: IWbwRequest = {
          book: book,
          para: para,
          channel_id: channelId,
          sn: wbwData[0].sn[0],
          data: arrSn.map((item) => {
            return {
              sn: item,
              words: wbwData
                .filter((value) => value.sn[0] === item)
                .map(wbwToXml),
            };
          }),
        };

        postWord(postParam);
      },
      [book, para, channelId, wbwToXml, postWord]
    );

    const saveWord = useCallback(
      (wbwData: IWbw[], sn: number) => {
        if (channelType === "nissaya") {
          return;
        }

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
      },
      [channelType, book, para, channelId, wbwToXml, postWord]
    );

    const magicDictLookup = useCallback(() => {
      const _lang = GetUserSetting("setting.dict.lang", settings);
      const url = `/v2/wbwlookup`;

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
    }, [
      settings,
      book,
      para,
      wordStart,
      wordEnd,
      wordData,
      channelId,
      channelType,
      update,
      saveWbwAll,
      onMagicDictDone,
    ]);

    const wbwPublish = useCallback(
      (wbwData: IWbw[], isPublic: boolean) => {
        const wordData: IDictRequest[] = [];

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
      },
      [channelLang, intl]
    );

    const resetWbw = useCallback(() => {
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
    }, [wordData, update, saveWbwAll]);

    const deleteWbw = useCallback(() => {
      const url = `/v2/wbw-sentence/${sentId}?channel=${channelId}`;
      console.info("api request", url);
      setLoading(true);
      delete_<IDeleteResponse>(url)
        .then((json) => {
          console.debug("api response", json);
          if (json.ok) {
            message.success(
              intl.formatMessage(
                { id: "message.delete.success" },
                { count: json.data }
              )
            );
          } else {
            message.error(json.message);
          }
        })
        .finally(() => setLoading(false))
        .catch((e) => console.log("Oops errors!", e));
    }, [sentId, channelId, intl]);

    const loadAnswer = useCallback(() => {
      if (courseAnswer || !course) {
        return;
      }

      let url = `/v2/wbw-sentence?view=course-answer`;
      url += `&book=${book}&para=${para}&wordStart=${wordStart}&wordEnd=${wordEnd}`;
      url += `&course=${course.courseId}`;

      setLoading(true);
      console.info("wbw sentence api request", url);
      get<ISentenceWbwListResponse>(url)
        .then((json) => {
          console.info("wbw sentence api response", json);
          if (json.ok) {
            if (json.data.rows.length > 0 && json.data.rows[0].origin) {
              const response = json.data.rows[0].origin[0];
              setCourseAnswer(
                response ? JSON.parse(response.content ?? "") : undefined
              );
            }
          }
        })
        .finally(() => setLoading(false));
    }, [courseAnswer, course, book, para, wordStart, wordEnd]);

    // ============ Effects ============

    // 优化11: 合并 AI 数据更新到单个 effect
    useEffect(() => {
      if (wbwData.length === 0) return;

      setWordData((origin) => {
        const wbwMap = createSnIndexMap(wbwData);

        return origin.map((item) => {
          const snKey = createSnKey(item.sn);
          const aiWbw =
            wbwMap.get(snKey) ||
            wbwData.find((v) => v.real.value === item.real.value);

          if (!aiWbw) return item;

          const newItem = { ...item };

          if (newItem.meaning && aiWbw.meaning) {
            newItem.meaning = {
              ...newItem.meaning,
              value: aiWbw.meaning.value,
            };
          }
          if (newItem.factors && aiWbw.factors) {
            newItem.factors = {
              ...newItem.factors,
              value: aiWbw.factors.value,
            };
          }
          if (newItem.factorMeaning && aiWbw.factorMeaning) {
            newItem.factorMeaning = {
              ...newItem.factorMeaning,
              value: aiWbw.factorMeaning.value,
            };
          }
          if (newItem.parent && aiWbw.parent?.value) {
            newItem.parent = { ...newItem.parent, value: aiWbw.parent.value };
          }
          if (newItem.type && aiWbw.type?.value) {
            newItem.type = {
              ...newItem.type,
              value: aiWbw.type.value.replaceAll(" ", ""),
            };

            if (newItem.grammar && aiWbw.grammar?.value) {
              newItem.grammar = {
                ...newItem.grammar,
                value: aiWbw.grammar.value.replaceAll(" ", ""),
              };

              if (newItem.case?.value === "") {
                newItem.case = {
                  ...newItem.case,
                  value: `${aiWbw.type.value}#${aiWbw.grammar.value}`,
                };
              }
            }
          }

          return newItem;
        });
      });
    }, [wbwData]);

    useEffect(() => setShowProgress(wbwProgress), [wbwProgress]);

    useEffect(() => {
      if (refreshable) {
        setWordData(paraMark(data));
      }
    }, [data, refreshable]);

    // 优化12: 优化单词发布逻辑
    useEffect(() => {
      const words = new Set<string>();

      wordData
        .filter(
          (value) =>
            value.type?.value !== null &&
            value.type?.value !== ".ctl." &&
            value.real.value &&
            value.real.value.length > 0
        )
        .forEach((value) => {
          if (value.real.value) {
            words.add(value.real.value);
          }
          if (value.parent?.value) {
            words.add(value.parent.value);
          }
        });

      const pubWords = Array.from(words);
      store.dispatch(add({ sentId, words: pubWords }));
    }, [sentId, wordData]);

    useEffect(() => {
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

    // ============ Render Logic ============

    const wordSplit = useCallback(
      (id: number, hyphen = "-") => {
        let factors = wordData[id]?.factors?.value;
        if (typeof factors !== "string") return;

        let sFm = wordData[id]?.factorMeaning?.value;
        if (typeof sFm === "undefined" || sFm === null) {
          sFm = new Array(factors.split("+").length).fill("").join("+");
        }

        if (wordData[id].case?.value?.split("#")[0] === ".un.") {
          factors = `[+${factors}+]`;
          sFm = `+${sFm}+`;
        } else if (hyphen !== "") {
          factors = factors.replaceAll("+", `+${hyphen}+`);
          sFm = sFm.replaceAll("+", `+${hyphen}+`);
        }

        const fm = sFm.split("+");

        const children: IWbw[] = factors.split("+").map((item, index) => {
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

        console.log("children", children);
        const newData: IWbw[] = [...wordData];
        newData.splice(id + 1, 0, ...children);
        console.log("new-data", newData);
        update(newData);
        saveWord(newData, wordData[id].sn[0]);
      },
      [wordData, update, saveWord]
    );

    // 优化13: 使用 memo 包装 WbwWord 渲染
    const wbwRender = useCallback(
      (
        item: IWbw,
        id: number,
        options?: { studio?: IStudio; answer?: IWbw }
      ) => {
        console.log("test wbw word render", item.word.value);
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
              setWordData((origin) => {
                const newData = [...origin];
                const snKey = createSnKey(e.sn);

                // 更新当前单词
                const index = newData.findIndex(
                  (v) => createSnKey(v.sn) === snKey
                );
                if (index !== -1) {
                  newData[index] = e;
                }

                // 如果是拆分后的单词,更新父单词的 factorMeaning
                if (e.sn.length > 1) {
                  const parentSn = e.sn.slice(0, e.sn.length - 1);
                  const parentSnKey = createSnKey(parentSn);

                  const factorMeaning = newData
                    .filter(
                      (value) =>
                        value.sn.length === e.sn.length &&
                        createSnKey(value.sn.slice(0, e.sn.length - 1)) ===
                          parentSnKey &&
                        value.real.value &&
                        value.real.value.length > 0
                    )
                    .map((item) => item.meaning?.value)
                    .join("+");

                  const parentIndex = newData.findIndex(
                    (v) => createSnKey(v.sn) === parentSnKey
                  );
                  if (parentIndex !== -1) {
                    newData[parentIndex] = {
                      ...newData[parentIndex],
                      factorMeaning: {
                        value: factorMeaning,
                        status: 5,
                      },
                    };

                    if (
                      newData[parentIndex].meaning?.status !== WbwStatus.manual
                    ) {
                      newData[parentIndex].meaning = {
                        value: factorMeaning.replaceAll("+", " "),
                        status: 5,
                      };
                    }
                  }
                }

                return newData;
              });

              // 延迟保存以批量处理
              setTimeout(() => {
                saveWord(wordData, e.sn[0]);
              }, 100);

              if (isPublish === true) {
                wbwPublish([e], isPublic);
              }
            }}
            onSplit={() => {
              const hasChildren =
                id < wordData.length - 1 &&
                createSnKey(wordData[id + 1].sn).startsWith(
                  createSnKey(wordData[id].sn) + ","
                );

              if (hasChildren) {
                // 合并
                console.log("合并");
                const parentSnKey = createSnKey(wordData[id].sn);
                const compactData = wordData.filter((value, index) => {
                  if (index === id) return true;
                  return !createSnKey(value.sn).startsWith(parentSnKey + ",");
                });
                update(compactData);
                saveWord(compactData, wordData[id].sn[0]);
              } else {
                // 拆开
                console.log("拆开");
                wordSplit(id);
              }
            }}
          />
        );
      },
      [
        channelId,
        displayMode,
        wbwMode,
        fieldDisplay,
        studio,
        readonly,
        wordData,
        saveWord,
        wbwPublish,
        update,
        wordSplit,
      ]
    );

    // 优化14: 缓存处理后的渲染数据
    const enrichedWordData = useMemo(() => {
      return wordData.map((item) => {
        // 检查是否有 AI 更新
        const snKey = createSnKey(item.sn);
        const newData = wbwData.find(
          (v) => createSnKey(v.sn) === snKey || v.real.value === item.real.value
        );

        let enrichedItem = newData ?? item;

        // 添加语法匹配
        const spell = enrichedItem.real.value;
        if (spell) {
          const grammarId = grammarMap.get(spell);
          if (grammarId) {
            enrichedItem = { ...enrichedItem, grammarId };
          }
        }

        return enrichedItem;
      });
    }, [wordData, wbwData, grammarMap]);

    // 优化15: 缓存水平布局渲染
    const horizontalLayout = useMemo(() => {
      if (layoutDirection !== "h") return null;

      const aa = courseAnswer ?? answer;
      const answerMap = aa ? createSnIndexMap(aa) : null;

      return enrichedWordData.map((item, id) => {
        const currAnswer = answerMap?.get(createSnKey(item.sn));
        return wbwRender(item, id, {
          studio: studio,
          answer: check ? currAnswer : undefined,
        });
      });
    }, [
      layoutDirection,
      enrichedWordData,
      courseAnswer,
      answer,
      check,
      studio,
      wbwRender,
    ]);

    // 优化16: 缓存树形布局数据
    const treeData = useMemo(() => {
      if (layoutDirection !== "v") return null;

      return wordData
        .filter((value) => value.sn.length === 1)
        .map((item, id) => {
          const children = wordData.filter(
            (value) => value.sn.length === 2 && value.sn[0] === item.sn[0]
          );

          return {
            title: wbwRender(item, id),
            key: createSnKey(item.sn),
            isLeaf: !item.factors?.value?.includes("+"),
            children:
              children.length > 0
                ? children.map((childItem, childId) => ({
                    title: wbwRender(childItem, childId),
                    key: createSnKey(childItem.sn),
                    isLeaf: true,
                  }))
                : undefined,
          };
        });
    }, [layoutDirection, wordData, wbwRender]);

    // 菜单项配置
    const menuItems = useMemo(
      () => [
        {
          key: "magic-dict-current",
          label: intl.formatMessage({ id: "buttons.magic-dict" }),
        },
        {
          key: "ai-magic-dict-current",
          label: "ai-magic-dict",
          disabled: !wbwModel,
        },
        {
          key: "progress",
          label: "显示/隐藏进度条",
        },
        {
          key: "check",
          label: "显示/隐藏错误提示",
        },
        {
          key: "wbw-dict-publish-all",
          label: "发布全部单词",
        },
        {
          type: "divider" as const,
        },
        {
          key: "copy-text",
          label: intl.formatMessage({ id: "buttons.copy.pali.text" }),
        },
        {
          key: "reset",
          label: intl.formatMessage({ id: "buttons.reset.wbw" }),
          danger: true,
        },
        {
          type: "divider" as const,
        },
        {
          key: "delete",
          label: intl.formatMessage({ id: "buttons.delete.wbw.sentence" }),
          danger: true,
          disabled: true,
        },
      ],
      [intl, wbwModel]
    );

    const handleMenuClick = useCallback(
      ({ key }: { key: string }) => {
        console.log(`Click on item ${key}`);
        switch (key) {
          case "magic-dict-current":
            setLoading(true);
            magicDictLookup();
            break;
          case "ai-magic-dict-current":
            if (wbwModel) {
              processStream(wbwModel.uid, wordData);
            }
            break;
          case "wbw-dict-publish-all":
            wbwPublish(wordData, user?.roles?.includes("basic") ? false : true);
            break;
          case "copy-text": {
            const paliText = wordData
              .filter((value) => value.type?.value !== ".ctl.")
              .map((item) => item.word.value)
              .join(" ");
            navigator.clipboard.writeText(paliText).then(() => {
              message.success("已经拷贝到剪贴板");
            });
            break;
          }
          case "progress":
            setShowProgress((origin) => !origin);
            break;
          case "check":
            loadAnswer();
            setCheck(!check);
            break;
          case "reset":
            modal.confirm({
              title: "清除逐词解析数据",
              icon: <ExclamationCircleOutlined />,
              content: "清除这个句子的逐词解析数据，此操作不可恢复",
              okText: "确认",
              cancelText: "取消",
              onOk: resetWbw,
            });
            break;
          case "delete":
            modal.confirm({
              title: "清除逐词解析数据",
              icon: <ExclamationCircleOutlined />,
              content: "删除整句的逐词解析数据，此操作不可恢复",
              okText: "确认",
              cancelText: "取消",
              onOk: deleteWbw,
            });
            break;
        }
      },
      [
        magicDictLookup,
        wbwModel,
        processStream,
        wordData,
        wbwPublish,
        user,
        loadAnswer,
        check,
        resetWbw,
        deleteWbw,
      ]
    );

    // ============ Render ============
    return (
      <div style={{ width: "100%" }}>
        <div
          style={{
            display: showProgress ? "flex" : "none",
            justifyContent: "space-between",
          }}
        >
          <div className="progress" style={{ width: 400 }}>
            <Progress percent={progress} size="small" />
          </div>

          <Space>
            <Studio data={studio} hideAvatar />
            <TimeShow updatedAt={updatedAt.toString()} />
          </Space>
        </div>

        {error && <Alert title={error} />}

        {isProcessing && (
          <div>
            <Progress
              percent={Math.round((wbwData.length * 100) / wordData.length)}
              size="small"
            />
          </div>
        )}

        <div className={`layout-${layoutDirection}`}>
          <Dropdown
            menu={{
              items: menuItems,
              onClick: handleMenuClick,
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
            horizontalLayout
          ) : (
            <Tree
              selectable={true}
              blockNode
              treeData={treeData || []}
              loadData={({ key }: any) =>
                new Promise<void>((resolve) => {
                  const index = wordData.findIndex(
                    (item) => createSnKey(item.sn) === key
                  );
                  if (index !== -1) {
                    wordSplit(index, "");
                  }
                  resolve();
                })
              }
            />
          )}
        </div>
      </div>
    );
  }
);

WbwSentCtl.displayName = "WbwSentCtl";

// ============ Widget 组件 ============

interface IWidgetWbwSent {
  props: string;
}

const WbwSentWidget = memo(({ props }: IWidgetWbwSent) => {
  const prop = useMemo(() => JSON.parse(atob(props)) as IWidget, [props]);
  return <WbwSentCtl {...prop} />;
});

WbwSentWidget.displayName = "WbwSentWidget";

export default WbwSentWidget;
