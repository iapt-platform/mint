import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { Divider } from "antd";

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

interface IWidget {
  initValue?: ISentence;
  value?: ISentence;
  wordWidget?: boolean;
  isPr?: boolean;
  editMode?: boolean;
  compact?: boolean;
  onChange?: Function;
}
const SentCellWidget = ({
  initValue,
  value,
  wordWidget = false,
  isPr = false,
  editMode = false,
  compact = false,
  onChange,
}: IWidget) => {
  const intl = useIntl();
  const [isEditMode, setIsEditMode] = useState(editMode);
  const [sentData, setSentData] = useState<ISentence | undefined>(initValue);
  const endings = useAppSelector(getEnding);
  const acceptPr = useAppSelector(sentence);
  const [prOpen, setPrOpen] = useState(false);

  console.log("load", initValue, value);

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

  const sid = `${sentData?.book}_${sentData?.para}_${sentData?.wordStart}_${sentData?.wordEnd}_${sentData?.channel.id}`;

  return (
    <div style={{ marginBottom: "8px" }}>
      {isPr ? undefined : (
        <div
          dangerouslySetInnerHTML={{
            __html: `<div class="tran_sent" id="${sid}" ></div>`,
          }}
        />
      )}
      <SentEditMenu
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
                    setSentData(data);
                    setIsEditMode(false);
                    if (typeof onChange !== "undefined") {
                      onChange(data);
                    }
                  }}
                />
              )
            ) : (
              <MdView
                style={{
                  width: "100%",
                  paddingLeft: compact ? 0 : "2em",
                  marginBottom: 0,
                }}
                placeholder="请输入"
                html={sentData.html}
                wordWidget={wordWidget}
              />
            )}
            {sentData.id ? (
              <SuggestionToolbar
                style={{ marginLeft: "2em" }}
                compact={compact}
                data={sentData}
                isPr={isPr}
                prOpen={prOpen}
                onPrClose={() => setPrOpen(false)}
              />
            ) : undefined}
          </div>
        ) : undefined}
      </SentEditMenu>
      {compact ? undefined : <Divider style={{ margin: "10px 0" }} />}
    </div>
  );
};

export default SentCellWidget;
