import { Button } from "antd";
import { CloseOutlined } from "@ant-design/icons";

import { SentEditInner, type IWidgetSentEditInner } from "../SentEdit";
import type { ISentence } from "../../../api/sentence";

interface IWidget {
  data?: IWidgetSentEditInner;
  onTranslationChange?: (data: ISentence) => void;
  onClose?: () => void;
}
const EditPad = ({ data, onTranslationChange, onClose }: IWidget) => {
  return (
    <div>
      <div style={{ textAlign: "right" }}>
        <Button size="small" icon={<CloseOutlined />} onClick={onClose}>
          返回审阅模式
        </Button>
      </div>

      {data ? (
        <SentEditInner
          mode="edit"
          {...data}
          onTranslationChange={onTranslationChange}
        />
      ) : (
        "无数据"
      )}
    </div>
  );
};

export default EditPad;
