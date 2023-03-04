import { Input, Divider } from "antd";
import { UploadFile } from "antd/es/upload/interface";
import { IAttachmentResponse } from "../../api/Attachments";
import WbwDetailUpload from "./WbwDetailUpload";

import { IWbw } from "./WbwWord";

interface IWidget {
  data: IWbw;
  onChange?: Function;
  onUpload?: Function;
}
const Widget = ({ data, onChange, onUpload }: IWidget) => {
  const onWordChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => {
    console.log("onWordChange:", e.target.value);
    if (typeof onChange !== "undefined") {
      onChange({ field: "word", value: e.target.value });
    }
  };
  const onRealChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
  ) => {
    console.log("onRealChange:", e.target.value);
    if (typeof onChange !== "undefined") {
      onChange({ field: "real", value: e.target.value });
    }
  };
  return (
    <div>
      <WbwDetailUpload
        data={data}
        onUpload={(fileList: UploadFile<IAttachmentResponse>[]) => {
          if (typeof onUpload !== "undefined") {
            onUpload(fileList);
          }
        }}
      />
    </div>
  );
};

export default Widget;
