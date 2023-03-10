import { Button } from "antd";
import { useState } from "react";
import { PlusOutlined } from "@ant-design/icons";

import { ISentence } from "../SentEdit";
import SentCellEditable from "./SentCellEditable";

interface IWidget {
  data: ISentence;
  onCreate?: Function;
}
const Widget = ({ data, onCreate }: IWidget) => {
  const [isEditMode, setIsEditMode] = useState(false);
  const [sentData, setSentData] = useState<ISentence>(data);

  return (
    <>
      <div style={{ display: isEditMode ? "none" : "block" }}>
        <Button
          type="dashed"
          style={{ width: 300 }}
          icon={<PlusOutlined />}
          onClick={() => {
            setIsEditMode(true);
          }}
        >
          添加修改建议
        </Button>
      </div>
      <div style={{ display: isEditMode ? "block" : "none" }}>
        <SentCellEditable
          data={sentData}
          isPr={true}
          onClose={() => {
            setIsEditMode(false);
          }}
          onCreate={() => {
            if (typeof onCreate !== "undefined") {
              onCreate();
            }
          }}
        />
      </div>
    </>
  );
};

export default Widget;
