import { Space } from "antd";
import { useState } from "react";
import { Typography } from "antd";
import User from "../../auth/User";
import TimeShow from "../../utilities/TimeShow";
import { ISentence } from "../SentEdit";
import SentEditMenu from "./SentEditMenu";
import SentCellEditable from "./SentCellEditable";
import MdView from "../MdView";

const { Text } = Typography;

interface ISentCell {
  data: ISentence;
  wordWidget?: boolean;
}
const Widget = ({ data, wordWidget = false }: ISentCell) => {
  const [isEditMode, setIsEditMode] = useState(false);
  const [sentData, setSentData] = useState<ISentence>(data);
  return (
    <SentEditMenu
      onModeChange={(mode: string) => {
        if (mode === "edit") {
          setIsEditMode(true);
        }
      }}
    >
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
        <Text type="secondary">
          <Space>
            <User {...sentData.editor} />
            <span>updated</span>
            <TimeShow time={sentData.updateAt} title="UpdatedAt" />
          </Space>
        </Text>
      </div>
    </SentEditMenu>
  );
};

export default Widget;
