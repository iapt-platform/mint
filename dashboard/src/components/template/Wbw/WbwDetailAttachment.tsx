import { UploadFile } from "antd/es/upload/interface";
import { IAttachmentResponse } from "../../api/Attachments";
import WbwDetailUpload from "./WbwDetailUpload";

import { IWbw } from "./WbwWord";

interface IWidget {
  data: IWbw;
  onChange?: Function;
  onUpload?: Function;
}
const WbwDetailAttachmentWidget = ({ data, onChange, onUpload }: IWidget) => {
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

export default WbwDetailAttachmentWidget;
