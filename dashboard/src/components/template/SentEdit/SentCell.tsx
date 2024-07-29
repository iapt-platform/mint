import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { Divider, message as AntdMessage, Modal } from "antd";
import { ExclamationCircleOutlined, LoadingOutlined } from "@ant-design/icons";

import { ISentence } from "../SentEdit";
import SentEditMenu from "./SentEditMenu";
import SentCellEditable from "./SentCellEditable";
import MdView from "../MdView";
import EditInfo, { Details } from "./EditInfo";
import SuggestionToolbar from "./SuggestionToolbar";
import { useAppSelector } from "../../../hooks";
import { accept, doneSent, done, sentence } from "../../../reducers/accept-pr";
import { IWbw } from "../Wbw/WbwWord";
import { my_to_roman } from "../../code/my";
import SentWbwEdit, { sentSave } from "./SentWbwEdit";
import { getEnding } from "../../../reducers/nissaya-ending-vocabulary";
import { nissayaBase } from "../Nissaya/NissayaMeaning";
import { anchor, message } from "../../../reducers/discussion";
import TextDiff from "../../general/TextDiff";
import { sentSave as _sentSave } from "./SentCellEditable";
import { IDeleteResponse } from "../../api/Article";
import { delete_, get } from "../../../request";

import "./style.css";
import StudioName from "../../auth/Studio";
import CopyToModal from "../../channel/CopyToModal";
import store from "../../../store";
import { randomString } from "../../../utils";
import User from "../../auth/User";
import { ISentenceListResponse } from "../../api/Corpus";
import { toISentence } from "./SentCanRead";

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
  onDelete?: Function;
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
  const [sentData, setSentData] = useState<ISentence | undefined>(initValue);
  const [bgColor, setBgColor] = useState<string>();
  const [loading, setLoading] = useState(false);
  const [uuid] = useState(randomString());
  const endings = useAppSelector(getEnding);
  const acceptPr = useAppSelector(sentence);
  const changedSent = useAppSelector(doneSent);

  const [prOpen, setPrOpen] = useState(false);
  const discussionMessage = useAppSelector(message);
  const anchorInfo = useAppSelector(anchor);
  const [copyOpen, setCopyOpen] = useState<boolean>(false);

  const sentId = `${sentData?.book}-${sentData?.para}-${sentData?.wordStart}-${sentData?.wordEnd}`;
  const sid = `${sentData?.book}_${sentData?.para}_${sentData?.wordStart}_${sentData?.wordEnd}_${sentData?.channel?.id}`;

  useEffect(() => {
    if (
      discussionMessage &&
      discussionMessage.resId &&
      discussionMessage.resId === initValue?.id
    ) {
      setBgColor("#1890ff33");
    } else {
      setBgColor(undefined);
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

  useEffect(() => {
    if (value) {
      setSentData(value);
    }
  }, [value]);

  useEffect(() => {
    console.debug("sent cell acceptPr", acceptPr, uuid);
    if (isPr) {
      console.debug("sent cell is pr");
      return;
    }
    if (typeof acceptPr === "undefined" || acceptPr.length === 0) {
      console.debug("sent cell acceptPr is empty");
      return;
    }
    if (!sentData) {
      console.debug("sent cell sentData is empty");
      return;
    }
    if (changedSent?.includes(uuid)) {
      console.debug("sent cell already apply", uuid);
      return;
    }

    const found = acceptPr
      .filter((value) => typeof value !== "undefined")
      .find((value) => {
        const vId = `${value.book}_${value.para}_${value.wordStart}_${value.wordEnd}_${value.channel.id}`;
        return vId === sid;
      });
    if (typeof found !== "undefined") {
      console.debug("sent cell sentence apply", uuid, found, found);
      setSentData(found);
      store.dispatch(done(uuid));
    }
  }, [acceptPr, sentData, isPr, uuid, changedSent, sid]);

  const deletePr = (id: string) => {
    delete_<IDeleteResponse>(`/v2/sentpr/${id}`)
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
    let url = `/v2/sentence?view=channel&sentence=${sentId}&html=true`;
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
                  _sentSave(
                    sentData,
                    (res: ISentence) => {
                      //setSentData(res);
                      //发布句子的改变，让同样的句子更新
                      store.dispatch(accept([res]));
                      if (typeof onChange !== "undefined") {
                        onChange(res);
                      }
                    },
                    () => {}
                  );
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
            case "json":
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
                  `/v2/snowflake?count=${wbw.length}`
                );
                wbw.forEach((value: IWbw, index: number, array: IWbw[]) => {
                  array[index].uid = snowflake.data.rows[index];
                });
              }

              if (sentData) {
                const newData = JSON.parse(JSON.stringify(sentData));
                newData.contentType = "json";
                newData.content = JSON.stringify(wbw);
                setSentData(newData);
                sentSave(newData, intl);
              }

              setIsEditMode(true);
              break;
            case "markdown":
              Modal.confirm({
                title: "格式转换",
                content:
                  "转换为markdown格式后，拆分意思数据会丢失。确定要转换吗？",
                onOk() {
                  if (sentData) {
                    let newData = JSON.parse(JSON.stringify(sentData));
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
                    sentSave(newData, intl);
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
      {compact ? undefined : <Divider style={{ margin: "10px 0" }} />}
      <CopyToModal
        important
        sentencesId={[sentId]}
        channel={sentData?.channel}
        open={copyOpen}
        onClose={() => setCopyOpen(false)}
      />
    </div>
  );
};

export default SentCellWidget;
