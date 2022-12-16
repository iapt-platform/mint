import { useState } from "react";

import { ISentence } from "../SentEdit";
import SentEditMenu from "./SentEditMenu";
import SentCellEditable from "./SentCellEditable";
import MdView from "../MdView";
import SuggestionTabs from "./SuggestionTabs";
import EditInfo from "./EditInfo";

interface ISentCell {
  data: ISentence;
  wordWidget?: boolean;
}
const Widget = ({ data, wordWidget = false }: ISentCell) => {
  const [isEditMode, setIsEditMode] = useState(false);
  const [sentData, setSentData] = useState<ISentence>(data);

  return (
    <div style={{ marginBottom: "8px" }}>
      <SentEditMenu
        onModeChange={(mode: string) => {
          if (mode === "edit") {
            setIsEditMode(true);
          }
        }}
      >
        <EditInfo data={data} />
        <div style={{ display: isEditMode ? "none" : "block" }}>
          <MdView
            html={sentData.html !== "" ? sentData.html : "请输入"}
            wordWidget={wordWidget}
          />
        </div>
        <div style={{ display: isEditMode ? "block" : "none" }}>
          <SentCellEditable
            data={sentData}
            onClose={() => {
              setIsEditMode(false);
            }}
            onDataChange={(data: ISentence) => {
              setSentData(data);
            }}
          />
        </div>

        <div>
          <SuggestionTabs data={data} />
        </div>
      </SentEditMenu>
    </div>
  );
};

export default Widget;
