import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { Divider, message as AntdMessage, Modal } from "antd";
import { ExclamationCircleOutlined } from "@ant-design/icons";

import { ISentence } from "../SentEdit";
import SentEditMenu from "./SentEditMenu";
import SentCellEditable from "./SentCellEditable";
import MdView from "../MdView";
import EditInfo from "./EditInfo";
import SuggestionToolbar from "./SuggestionToolbar";
import { useAppSelector } from "../../../hooks";
import { sentence } from "../../../reducers/accept-pr";
import { IWbw } from "../Wbw/WbwWord";
import { my_to_roman } from "../../code/my";
import SentWbwEdit, { sentSave } from "./SentWbwEdit";
import { getEnding } from "../../../reducers/nissaya-ending-vocabulary";
import { nissayaBase } from "../Nissaya/NissayaMeaning";
import { anchor, message } from "../../../reducers/discussion";
import TextDiff from "../../general/TextDiff";
import { sentSave as _sentSave } from "./SentCellEditable";
import { IDeleteResponse } from "../../api/Article";
import { delete_ } from "../../../request";

interface IWidget {
  initValue?: ISentence;
  value?: ISentence;
  wordWidget?: boolean;
  isPr?: boolean;
  editMode?: boolean;
  compact?: boolean;
  showDiff?: boolean;
  diffText?: string | null;
  onChange?: Function;
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
  const intl = useIntl();
  const [isEditMode, setIsEditMode] = useState(editMode);
  const [sentData, setSentData] = useState<ISentence | undefined>(initValue);
  const [bgColor, setBgColor] = useState<string>();
  const endings = useAppSelector(getEnding);
  const acceptPr = useAppSelector(sentence);
  const [prOpen, setPrOpen] = useState(false);
  const discussionMessage = useAppSelector(message);
  const anchorInfo = useAppSelector(anchor);
  const sid = `${sentData?.book}_${sentData?.para}_${sentData?.wordStart}_${sentData?.wordEnd}_${sentData?.channel.id}`;
  useEffect(() => {
    if (
      discussionMessage &&
      discussionMessage.resId &&
      discussionMessage.resId === initValue?.id
    ) {
      setBgColor("#1890ff33");
    } else {
      setBgColor("unset");
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
    if (typeof acceptPr !== "undefined" && !isPr && sentData) {
      if (
        acceptPr.book === sentData.book &&
        acceptPr.para === sentData.para &&
        acceptPr.wordStart === sentData.wordStart &&
        acceptPr.wordEnd === sentData.wordEnd &&
        acceptPr.channel.id === sentData.channel.id
      )
        setSentData(acceptPr);
    }
  }, [acceptPr, sentData, isPr]);

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

  return (
    <div style={{ marginBottom: "8px", backgroundColor: bgColor }}>
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
            case "suggestion":
              setPrOpen(true);
              break;
            case "paste":
              navigator.clipboard.readText().then((value: string) => {
                if (sentData && value !== "") {
                  sentData.content = value;
                  _sentSave(
                    sentData,
                    (res) => {
                      setSentData(res);
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
        onConvert={(format: string) => {
          switch (format) {
            case "json":
              const wbw: IWbw[] = sentData?.content
                ? sentData.content.split("\n").map((item, id) => {
                    const parts = item.split("=");
                    const word = my_to_roman(parts[0]);
                    const meaning: string =
                      parts.length > 1 ? parts[1].trim() : "";
                    let parent: string = "";
                    let factors: string = "";
                    if (!meaning.includes(" ") && endings) {
                      const base = nissayaBase(meaning, endings);
                      parent = base.base;
                      const end = base.ending ? base.ending : [];
                      factors = [base.base, ...end].join("+");
                    } else {
                      factors = meaning.replaceAll(" ", "+");
                    }
                    return {
                      book: sentData.book,
                      para: sentData.para,
                      sn: [id],
                      word: { value: word ? word : parts[0], status: 0 },
                      real: { value: meaning, status: 0 },
                      meaning: { value: "", status: 0 },
                      parent: { value: parent, status: 0 },
                      factors: {
                        value: factors,
                        status: 0,
                      },
                      confidence: 0.5,
                    };
                  })
                : [];
              setSentData((origin) => {
                if (origin) {
                  origin.contentType = "json";
                  origin.content = JSON.stringify(wbw);
                  sentSave(origin, intl);
                  return origin;
                }
              });
              setIsEditMode(true);
              break;
            case "markdown":
              setSentData((origin) => {
                if (origin) {
                  const wbwData: IWbw[] = origin.content
                    ? JSON.parse(origin.content)
                    : [];
                  const newContent = wbwData
                    .map((item) => {
                      return [
                        item.word.value,
                        item.real.value,
                        item.meaning?.value,
                      ].join("=");
                    })
                    .join("\n");
                  origin.content = newContent;
                  origin.contentType = "markdown";
                  sentSave(origin, intl);
                  return origin;
                }
              });
              setIsEditMode(true);
              break;
          }
        }}
      >
        {sentData ? (
          <div
            style={{
              display: "flex",
              flexDirection: compact ? "row" : "column",
              alignItems: "flex-start",
            }}
          >
            <EditInfo data={sentData} compact={compact} />
            {isEditMode ? (
              sentData?.contentType === "json" ? (
                <SentWbwEdit
                  data={sentData}
                  onClose={() => {
                    setIsEditMode(false);
                  }}
                  onSave={(data: ISentence) => {
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
                    setIsEditMode(false);
                    setSentData(data);
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
                style={{
                  width: "100%",
                  paddingLeft: compact ? 0 : "2em",
                  marginBottom: 0,
                }}
                placeholder="请输入"
                html={sentData.html ? sentData.html : sentData.content}
                wordWidget={wordWidget}
              />
            )}

            <SuggestionToolbar
              style={{ marginLeft: "2em" }}
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
        ) : undefined}
      </SentEditMenu>
      {compact ? undefined : <Divider style={{ margin: "10px 0" }} />}
    </div>
  );
};

export default SentCellWidget;
