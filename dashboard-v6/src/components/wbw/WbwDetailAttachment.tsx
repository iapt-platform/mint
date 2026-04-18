import type { IAttachmentRequest } from "../../api/Attachments";
import type { IWbw, IWbwAttachment } from "../../types/wbw";
import WbwDetailUpload from "./WbwDetailUpload";

interface IWidget {
  data: IWbw;
  onChange?: (value?: IWbwAttachment[]) => void;
  onUpload?: (fileList: IAttachmentRequest[]) => void;
  onDialogOpen?: (open: boolean) => void;
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
        onChange={(value) => {
          if (typeof onChange !== "undefined") {
            onChange(value);
          }
        }}
      />
    </div>
  );
};

export default WbwDetailAttachmentWidget;
