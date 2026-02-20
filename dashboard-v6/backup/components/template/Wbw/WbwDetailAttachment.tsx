import type { _UploadFile } from "antd/es/upload/interface";
import type {
  IAttachmentRequest,
  IAttachmentResponse,
} from "../../../api/Attachments"; // eslint-disable-line
import WbwDetailUpload from "./WbwDetailUpload";

import type { IWbw, IWbwAttachment } from "./WbwWord";

interface IWidget {
  data: IWbw;
  onChange?: Function;
  onUpload?: Function;
  onDialogOpen?: Function;
}
const WbwDetailAttachmentWidget = ({
  data,
  onChange,
  onUpload,
  onDialogOpen,
}: IWidget) => {
  return (
    <div>
      <WbwDetailUpload
        data={data}
        onUpload={(fileList: IAttachmentRequest[]) => {
          if (typeof onUpload !== "undefined") {
            onUpload(fileList);
          }
        }}
        onDialogOpen={(open: boolean) => {
          if (typeof onDialogOpen !== "undefined") {
            onDialogOpen(open);
          }
        }}
        onChange={(value: IWbwAttachment[]) => {
          if (typeof onChange !== "undefined") {
            onChange(value);
          }
        }}
      />
    </div>
  );
};

export default WbwDetailAttachmentWidget;
