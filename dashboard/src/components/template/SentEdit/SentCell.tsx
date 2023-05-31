import { useEffect, useState } from "react";

import { ISentence } from "../SentEdit";
import SentEditMenu from "./SentEditMenu";
import SentCellEditable from "./SentCellEditable";
import MdView from "../MdView";
import EditInfo from "./EditInfo";
import SuggestionToolbar from "./SuggestionToolbar";
import { Divider } from "antd";
import { useAppSelector } from "../../../hooks";
import { sentence } from "../../../reducers/accept-pr";
import { IWbw } from "../Wbw/WbwWord";
import { WbwSentCtl } from "../WbwSent";
import { my_to_roman } from "../../code/my";

interface ISentCell {
  data: ISentence;
  wordWidget?: boolean;
  isPr?: boolean;
}
const SentCellWidget = ({
  data,
  wordWidget = false,
  isPr = false,
}: ISentCell) => {
  const [isEditMode, setIsEditMode] = useState(false);
  const [sentData, setSentData] = useState<ISentence>(data);
  const [wbwData, setWbwData] = useState<IWbw[]>([]);

  const acceptPr = useAppSelector(sentence);
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
  const hasPr = sentData.suggestionCount?.suggestion ? "true" : "false";
  const hasDiscussion = sentData.suggestionCount?.discussion ? "true" : "false";
  return (
    <div style={{ marginBottom: "8px" }}>
      {isPr ? undefined : (
        <div
          dangerouslySetInnerHTML={{
            __html: `<div class="pr_icon" id="${sid}" has-pr="${hasPr}" has-disc="${hasDiscussion}" data-pr="${data.suggestionCount?.suggestion}"></div>`,
          }}
        />
      )}
      <SentEditMenu
        onModeChange={(mode: string) => {
          if (mode === "edit") {
            setIsEditMode(true);
          }
        }}
        onConvert={(format: string) => {
          console.log("format", format);
          switch (format) {
            case "json":
              const wbw: IWbw[] = data.content.split("\n").map((item, id) => {
                const parts = item.split("=");
                const word = my_to_roman(parts[0]);
                const meaning = parts.length > 1 ? parts[1].trim() : "";
                return {
                  book: data.book,
                  para: data.para,
                  sn: [id],
                  word: { value: word ? word : parts[0], status: 0 },
                  real: { value: meaning, status: 0 },
                  meaning: { value: "", status: 0 },
                  factors: {
                    value: meaning.replaceAll(" ", "+"),
                    status: 0,
                  },
                  confidence: 0.5,
                };
              });
              setWbwData(wbw);
              break;
          }
        }}
      >
        <EditInfo data={sentData} />
        <div
          style={{ display: isEditMode ? "none" : "block", marginLeft: "2em" }}
        >
          <MdView
            html={sentData.html !== "" ? sentData.html : "请输入"}
            wordWidget={wordWidget}
          />
        </div>
        <div style={{ display: isEditMode ? "block" : "none" }}>
          {wbwData.length > 0 ? (
            <WbwSentCtl
              book={data.book}
              para={data.para}
              wordStart={data.wordStart}
              wordEnd={data.wordEnd}
              data={wbwData}
              refreshable={true}
              display="block"
              fields={{
                meaning: true,
                factors: false,
                factorMeaning: false,
                case: true,
              }}
              channelId={data.channel.id}
              onChange={(data: IWbw[]) => {
                setWbwData(data);
              }}
            />
          ) : (
            <SentCellEditable
              data={sentData}
              isPr={isPr}
              onClose={() => {
                setIsEditMode(false);
              }}
              onDataChange={(data: ISentence) => {
                setSentData(data);
              }}
            />
          )}
        </div>

        <div style={{ marginLeft: "2em" }}>
          <SuggestionToolbar data={data} isPr={isPr} />
        </div>
      </SentEditMenu>
      <Divider style={{ margin: "10px 0" }} />
    </div>
  );
};

export default SentCellWidget;
