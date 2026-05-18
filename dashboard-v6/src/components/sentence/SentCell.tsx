import { useEffect, useMemo, useState } from "react";
import { useIntl } from "react-intl";
import { message as AntdMessage, Modal, Collapse } from "antd";
import { ExclamationCircleOutlined, LoadingOutlined } from "@ant-design/icons";

import type { ISentence } from "../../api/sentence";
import SentEditMenu from "./SentEditMenu";
import SentCellEditable from "./SentCellEditable";

import EditInfo, { Details } from "./EditInfo";
import SuggestionToolbar from "./SuggestionToolbar";
import { useAppSelector } from "../../hooks";
import { accept, doneSent, done, sentence } from "../../reducers/accept-pr";

import SentWbwEdit from "./SentWbwEdit";
import { getEnding } from "../../reducers/nissaya-ending-vocabulary";

import { anchor, message } from "../../reducers/discussion";
import TextDiff from "../general/TextDiff";

import type { IDeleteResponse } from "../../api/article";
import { delete_, get } from "../../request";

import "./style.css";
import StudioName from "../auth/Studio";
import CopyToModal from "../channel/CopyToModal";
import store from "../../store";
import { randomString } from "../../utils";
import User from "../auth/User";
import type { ISentenceListResponse } from "../../api/sentence";

import SentAttachment from "./SentAttachment";

import type { IWbw } from "../../types/wbw";
import { my_to_roman } from "../../utils/code/my";
import { nissayaBase } from "../nissaya/utils";
import { toISentence } from "../sentence/utils";
import NissayaSent from "../nissaya/NissayaSent";
import { sentSave } from "../../api/sentence";
import MdView from "../general/MdView";

interface ISnowFlakeResponse {
  ok: boolean;
  message?: string;
  data: {
    rows: string;
    count: number;
  };
}

interface IWidget {
  initValue?: ISentence;
  value?: ISentence;
  wordWidget?: boolean;
  isPr?: boolean;
  editMode?: boolean;
  compact?: boolean;
  showDiff?: boolean;
  diffText?: string | null;
  onChange?: (data: ISentence) => void;
  onDelete?: () => void;
}
const SentCellWidget = ({
  initValue,
  value,
  wordWidget = false,
  isPr = false,
  editMode = false,
  compact = false,
  showDiff = false,
  diffText,
  onChange,
  onDelete,
}: IWidget) => {
  console.debug("SentCell render", value);
  const intl = useIntl();
  const [isEditMode, setIsEditMode] = useState(editMode);
  // 用一个独立的 state 存储来自 acceptPr 的覆盖值
  const [overrideSentData, setOverrideSentData] = useState<
    ISentence | undefined
  >(undefined);
  const [loading, setLoading] = useState(false);
  const [uuid] = useState(randomString());
  const endings = useAppSelector(getEnding);
  const acceptPr = useAppSelector(sentence);
  const changedSent = useAppSelector(doneSent);

  const [prOpen, setPrOpen] = useState(false);
  const discussionMessage = useAppSelector(message);
  const anchorInfo = useAppSelector(anchor);
  const [copyOpen, setCopyOpen] = useState<boolean>(false);

  // sentData 由 useMemo 派生：优先级 overrideSentData > value > initValue
  const sentData = useMemo(() => {
    return overrideSentData ?? value ?? initValue;
  }, [overrideSentData, value, initValue]);

  const sentId = `${sentData?.book}-${sentData?.para}-${sentData?.wordStart}-${sentData?.wordEnd}`;
  const sid = `${sentData?.book}_${sentData?.para}_${sentData?.wordStart}_${sentData?.wordEnd}_${sentData?.channel?.id}`;

  const bgColor = useMemo(() => {
    if (
      discussionMessage &&
      discussionMessage.resId &&
      discussionMessage.resId === initValue?.id
    ) {
      return "#1890ff33";
    } else {
      return undefined;
    }
  }, [discussionMessage, initValue?.id]);

  useEffect(() => {
    if (anchorInfo && anchorInfo?.resId === initValue?.id) {
      const ele = document.getElementById(sid);
      if (ele !== null) {
        ele.scrollIntoView({
          behavior: "smooth",
          block: "center",
          inline: "nearest",
        });
      }
    }
  }, [anchorInfo, initValue?.id, sid]);

  // 保留一个 setter 供内部使用（如 refresh、paste、format convert 等）
  // 这些场景改为直接更新 overrideSentData
  const setSentData = (data: ISentence) => {
    setOverrideSentData(data);
  };

  useEffect(() => {
    console.debug("sent cell acceptPr", acceptPr, uuid);
    if (isPr) return;
    if (typeof acceptPr === "undefined" || acceptPr.length === 0) return;
    if (!sentData) return;
    if (changedSent?.includes(uuid)) return;

    const found = acceptPr
      .filter((value) => typeof value !== "undefined")
      .find((value) => {
        const vId = `${value.book}_${value.para}_${value.wordStart}_${value.wordEnd}_${value.channel.id}`;
        return vId === sid;
      });

    if (typeof found !== "undefined") {
      console.debug("sent cell sentence apply", uuid, found);
      // 用 setTimeout 将 setState 移出 effect 同步体，避免级联渲染
      setTimeout(() => {
        setOverrideSentData(found);
        store.dispatch(done(uuid));
      }, 0);
    }
  }, [acceptPr, sentData, isPr, uuid, changedSent, sid]);

  const deletePr = (id: string) => {
    delete_<IDeleteResponse>(`/api/v2/sentpr/${id}`)
      .then((json) => {
        if (json.ok) {
          AntdMessage.success("删除成功");
          if (typeof onDelete !== "undefined") {
            onDelete();
          }
        } else {
          AntdMessage.error(json.message);
        }
      })
      .catch((e) => console.log("Oops errors!", e));
  };

  const refresh = () => {
    if (typeof sentData === "undefined") {
      return;
    }
    let url = `/api/v2/sentence?view=channel&sentence=${sentId}&html=true`;
    url += `&channel=${sentData.channel.id}`;
    console.debug("api request", url);
    setLoading(true);
    get<ISentenceListResponse>(url)
      .then((json) => {
        console.debug("api response", json);

        if (json.ok && json.data.count > 0) {
          const newData: ISentence[] = json.data.rows.map((item) => {
            return toISentence(item, [sentData.channel.id]);
          });
          setSentData(newData[0]);
        }
      })
      .finally(() => setLoading(false));
  };

  console.debug("sentence data", sentData, value);

  return (
    <div style={{ marginBottom: "8px", backgroundColor: bgColor }}>
      {loading ? <LoadingOutlined /> : <></>}
      {isPr ? undefined : (
        <div
          dangerouslySetInnerHTML={{
            __html: `<div class="tran_sent" id="${sid}" ></div>`,
          }}
        />
      )}
      <SentEditMenu
        isPr={isPr}
        data={sentData}
        onModeChange={(mode: string) => {
          if (mode === "edit") {
            setIsEditMode(true);
          }
        }}
        onMenuClick={(key: string) => {
          switch (key) {
            case "refresh":
              refresh();
              break;
            case "copy-to":
              setCopyOpen(true);
              break;
            case "suggestion":
              setPrOpen(true);
              break;
            case "paste":
              navigator.clipboard.readText().then((value: string) => {
                if (sentData && value !== "") {
                  sentData.content = value;
                  const newSent = sentSave(sentData);
                  newSent.then((value) => {
                    //发布句子的改变，让同样的句子更新
                    if (value?.ok) {
                      const newData: ISentence = toISentence(value.data);
                      store.dispatch(accept([newData]));
                      if (typeof onChange !== "undefined") {
                        onChange(newData);
                      }
                    }
                  });
                }
              });
              break;
            case "delete":
              Modal.confirm({
                icon: <ExclamationCircleOutlined />,
                title: intl.formatMessage({
                  id: "message.delete.confirm",
                }),

                content: "",
                okText: intl.formatMessage({
                  id: "buttons.delete",
                }),
                okType: "danger",
                cancelText: intl.formatMessage({
                  id: "buttons.no",
                }),
                onOk() {
                  if (isPr && sentData && sentData.id) {
                    deletePr(sentData.id);
                  }
                },
              });
              break;
            default:
              break;
          }
        }}
        onConvert={async (format: string) => {
          switch (format) {
            case "json": {
              const wbw: IWbw[] = sentData?.content
                ? sentData.content
                    .split("\n")
                    .filter((value) => value.trim().length > 0)
                    .map((item, id) => {
                      const parts = item.split("=");
                      const word = my_to_roman(parts[0]);
                      const meaning: string =
                        parts.length > 1
                          ? parts[1]
                              .trim()
                              .replaceAll("။", "")
                              .replaceAll("(", " ( ")
                              .replaceAll(")", " ) ")
                          : "";
                      const translation: string =
                        parts.length > 2 ? parts[2].trim() : "";
                      let parent: string = "";
                      let factors: string = "";
                      const factor1 = meaning
                        .split(" ")
                        .filter((value) => value !== "");
                      factors = factor1
                        .map((item) => {
                          if (endings) {
                            const base = nissayaBase(item, endings);
                            if (factor1.length === 1) {
                              parent = base.base;
                            }
                            const end = base.ending ? base.ending : [];
                            return [base.base, ...end]
                              .filter((value) => value !== "")
                              .join("-");
                          } else {
                            return item;
                          }
                        })
                        .join("+");
                      return {
                        uid: "0",
                        book: sentData.book,
                        para: sentData.para,
                        sn: [id],
                        word: { value: word ? word : parts[0], status: 0 },
                        real: { value: meaning, status: 0 },
                        meaning: { value: translation, status: 0 },
                        parent: { value: parent, status: 0 },
                        factors: {
                          value: factors,
                          status: 0,
                        },
                        confidence: 0.5,
                      };
                    })
                : [];
              if (wbw.length > 0) {
                const snowflake = await get<ISnowFlakeResponse>(
                  `/api/v2/snowflake?count=${wbw.length}`
                );
                wbw.forEach((_value: IWbw, index: number, array: IWbw[]) => {
                  array[index].uid = snowflake.data.rows[index];
                });
              }

              if (sentData) {
                const newData = JSON.parse(JSON.stringify(sentData));
                newData.contentType = "json";
                newData.content = JSON.stringify(wbw);
                setSentData(newData);
                sentSave(newData);
              }

              setIsEditMode(true);
              break;
            }
            case "markdown":
              Modal.confirm({
                title: "格式转换",
                content:
                  "转换为markdown格式后，拆分意思数据会丢失。确定要转换吗？",
                onOk() {
                  if (sentData) {
                    const newData = JSON.parse(JSON.stringify(sentData));
                    const wbwData: IWbw[] = newData.content
                      ? JSON.parse(newData.content)
                      : [];
                    const newContent = wbwData
                      .filter((value) => value.sn.length === 1)
                      .map((item) => {
                        return [
                          item.word.value,
                          item.real.value,
                          item.meaning?.value,
                        ].join("=");
                      })
                      .join("\n");
                    newData.content = newContent;
                    newData["contentType"] = "markdown";
                    sentSave(newData);
                    setSentData(newData);
                  }
                  setIsEditMode(true);
                },
              });

              break;
          }
        }}
      >
        {sentData ? (
          <div style={{ display: "flex" }}>
            <div style={{ marginRight: 8 }}>
              {isPr ? (
                <User {...sentData.editor} showName={false} />
              ) : (
                <StudioName
                  data={sentData.studio}
                  hideName
                  popOver={
                    compact ? (
                      <Details data={sentData} isPr={isPr} />
                    ) : undefined
                  }
                />
              )}
            </div>
            <div
              style={{
                display: "flex",
                flexDirection: compact ? "row" : "column",
                alignItems: "flex-start",
                width: "100%",
              }}
            >
              {isEditMode ? (
                sentData?.contentType === "json" ? (
                  <SentWbwEdit
                    data={sentData}
                    onClose={() => {
                      setIsEditMode(false);
                    }}
                    onSave={(data: ISentence) => {
                      console.debug("sent cell onSave", data);
                      setSentData(data);
                    }}
                  />
                ) : (
                  <SentCellEditable
                    data={sentData}
                    isPr={isPr}
                    onClose={() => {
                      setIsEditMode(false);
                    }}
                    onSave={(data: ISentence) => {
                      console.debug("sent cell onSave", data);
                      //setSentData(data);
                      store.dispatch(accept([data]));
                      setIsEditMode(false);
                      if (typeof onChange !== "undefined") {
                        onChange(data);
                      }
                    }}
                  />
                )
              ) : showDiff ? (
                <TextDiff
                  showToolTip={false}
                  content={sentData.content}
                  oldContent={diffText}
                />
              ) : sentData.channel.type === "nissaya" &&
                sentData.contentType === "json" ? (
                <NissayaSent
                  data={JSON.parse(
                    sentData.content && sentData.content !== ""
                      ? sentData.content
                      : "[]"
                  )}
                />
              ) : (
                <MdView
                  className="sentence"
                  style={{
                    width: "100%",
                    marginBottom: 0,
                  }}
                  placeholder={intl.formatMessage({
                    id: "labels.input",
                  })}
                  html={sentData.html ? sentData.html : sentData.content}
                  wordWidget={wordWidget}
                />
              )}
              <div
                style={{
                  display: "flex",
                  justifyContent: "space-between",
                  width: compact ? undefined : "100%",
                  paddingRight: 20,
                  flexWrap: "wrap",
                }}
              >
                <EditInfo data={sentData} isPr={isPr} compact={compact} />
                <SuggestionToolbar
                  style={{
                    marginBottom: 0,
                    justifyContent: "flex-end",
                    marginLeft: "auto",
                  }}
                  compact={compact}
                  data={sentData}
                  isPr={isPr}
                  prOpen={prOpen}
                  onPrClose={() => setPrOpen(false)}
                  onDelete={() => {
                    if (isPr && sentData.id) {
                      deletePr(sentData.id);
                    }
                  }}
                />
              </div>
            </div>
          </div>
        ) : undefined}
      </SentEditMenu>

      <CopyToModal
        important
        sentencesId={[sentId]}
        channel={sentData?.channel}
        open={copyOpen}
        onClose={() => setCopyOpen(false)}
      />
      <Collapse
        bordered={false}
        style={{ display: "none", backgroundColor: "unset" }}
      >
        <Collapse.Panel
          header={"attachment"}
          key="parent2"
          style={{ backgroundColor: "unset" }}
        >
          <SentAttachment sentenceId={sentData?.id} />
        </Collapse.Panel>
      </Collapse>
    </div>
  );
};

export default SentCellWidget;
