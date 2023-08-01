import { useEffect, useState } from "react";

import { ISentence } from "../SentEdit";
import SentEditMenu from "./SentEditMenu";
import SentCellEditable from "./SentCellEditable";
import MdView from "../MdView";
import EditInfo from "./EditInfo";
import SuggestionToolbar from "./SuggestionToolbar";
import { Divider, Space } from "antd";
import { useAppSelector } from "../../../hooks";
import { sentence } from "../../../reducers/accept-pr";
import { IWbw } from "../Wbw/WbwWord";
import { my_to_roman } from "../../code/my";
import SentWbwEdit, { sentSave } from "./SentWbwEdit";
import { getEnding } from "../../../reducers/nissaya-ending-vocabulary";
import { nissayaBase } from "../Nissaya/NissayaMeaning";
import { useIntl } from "react-intl";

interface IWidget {
  data: ISentence;
  wordWidget?: boolean;
  isPr?: boolean;
  editMode?: boolean;
  compact?: boolean;
}
const SentCellWidget = ({
  data,
  wordWidget = false,
  isPr = false,
  editMode = false,
  compact = false,
}: IWidget) => {
  const intl = useIntl();
  const [isEditMode, setIsEditMode] = useState(editMode);
  const [sentData, setSentData] = useState<ISentence>(data);
  const endings = useAppSelector(getEnding);
  const acceptPr = useAppSelector(sentence);
  const [prOpen, setPrOpen] = useState(false);

  useEffect(() => {
    setSentData(data);
  }, [data]);

  useEffect(() => {
    if (typeof acceptPr !== "undefined" && !isPr) {
      if (
        acceptPr.book === data.book &&
        acceptPr.para === data.para &&
        acceptPr.wordStart === data.wordStart &&
        acceptPr.wordEnd === data.wordEnd &&
        acceptPr.channel.id === data.channel.id
      )
        setSentData(acceptPr);
    }
  }, [acceptPr, data, isPr]);
  const sid = `${sentData.book}_${sentData.para}_${sentData.wordStart}_${sentData.wordEnd}_${sentData.channel.id}`;

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
        data={data}
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
              const wbw: IWbw[] = data.content.split("\n").map((item, id) => {
                const parts = item.split("=");
                const word = my_to_roman(parts[0]);
                const meaning: string = parts.length > 1 ? parts[1].trim() : "";
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
                  book: data.book,
                  para: data.para,
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
              });
              setSentData((origin) => {
                origin.contentType = "json";
                origin.content = JSON.stringify(wbw);
                sentSave(origin, intl);
                return origin;
              });
              setIsEditMode(true);
              break;
            case "markdown":
              setSentData((origin) => {
                const wbwData: IWbw[] = JSON.parse(origin.content);
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
              });
              setIsEditMode(true);
              break;
          }
        }}
      >
        <Space
          direction={compact ? "horizontal" : "vertical"}
          style={{ alignItems: "flex-start" }}
        >
          <EditInfo data={sentData} compact={compact} />
          {isEditMode ? (
            <div>
              {sentData.contentType === "json" ? (
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
                  }}
                />
              )}
            </div>
          ) : (
            <MdView
              style={{ marginLeft: compact ? 0 : "2em" }}
              html={sentData.html !== "" ? sentData.html : "请输入"}
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
          />
        </Space>
      </SentEditMenu>
      {compact ? undefined : <Divider style={{ margin: "10px 0" }} />}
    </div>
  );
};

export default SentCellWidget;
