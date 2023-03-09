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

interface ISentCell {
  data: ISentence;
  wordWidget?: boolean;
  isPr?: boolean;
}
const Widget = ({ data, wordWidget = false, isPr = false }: ISentCell) => {
  const [isEditMode, setIsEditMode] = useState(false);
  const [sentData, setSentData] = useState<ISentence>(data);
  const acceptPr = useAppSelector(sentence);
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
  return (
    <div style={{ marginBottom: "8px" }}>
      <SentEditMenu
        onModeChange={(mode: string) => {
          if (mode === "edit") {
            setIsEditMode(true);
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
        </div>

        <div style={{ marginLeft: "2em" }}>
          <SuggestionToolbar data={data} isPr={isPr} />
        </div>
      </SentEditMenu>
      <Divider style={{ margin: "10px 0" }} />
    </div>
  );
};

export default Widget;
