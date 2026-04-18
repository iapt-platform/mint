import { DeleteOutlined } from "@ant-design/icons";
import { Button, List } from "antd";

import { currentUser } from "../../reducers/current-user";
import AttachmentDialog from "../attachment/AttachmentDialog";
import { useAppSelector } from "../../hooks";
import type { IAttachmentRequest } from "../../api/Attachments";
import type { IWbw, IWbwAttachment } from "../../types/wbw";

interface IWidget {
  data: IWbw;
  onUpload?: (value: IAttachmentRequest[]) => void;
  onChange?: (output?: IWbwAttachment[]) => void;
  onDialogOpen?: (open: boolean) => void;
}
const WbwDetailUploadWidget = ({
  data,
  onUpload,
  onChange,
  onDialogOpen,
}: IWidget) => {
  const user = useAppSelector(currentUser);
  const attachments = data.attachments;

  return (
    <>
      <List
        itemLayout="vertical"
        size="small"
        header={
          <AttachmentDialog
            trigger={<Button>上传</Button>}
            studioName={user?.realName}
            onOpenChange={(open: boolean) => {
              if (typeof onDialogOpen !== "undefined") {
                onDialogOpen(open);
              }
            }}
            onSelect={(value: IAttachmentRequest) => {
              onUpload?.([value]);
            }}
          />
        }
        dataSource={attachments}
        renderItem={(item, id) => (
          <List.Item>
            <div style={{ display: "flex", justifyContent: "space-between" }}>
              <div style={{ maxWidth: 400, overflowX: "hidden" }}>
                {item.title}
              </div>
              <div style={{ marginLeft: 20 }}>
                <Button
                  type="link"
                  size="small"
                  icon={<DeleteOutlined />}
                  onClick={() => {
                    const output = data.attachments?.filter(
                      (_value: IWbwAttachment, index: number) => index !== id
                    );
                    onChange?.(output);
                  }}
                />
              </div>
            </div>
          </List.Item>
        )}
      />
    </>
  );
};

export default WbwDetailUploadWidget;
