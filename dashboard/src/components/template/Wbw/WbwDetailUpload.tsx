import { useIntl } from "react-intl";
import { DeleteOutlined } from "@ant-design/icons";
import type { UploadProps } from "antd";
import { Button, List, message, Upload } from "antd";

import { API_HOST } from "../../../request";
import { currentUser, get as getToken } from "../../../reducers/current-user";
import { IWbw, IWbwAttachment } from "./WbwWord";
import AttachmentDialog from "../../attachment/AttachmentDialog";
import { useAppSelector } from "../../../hooks";
import { useState } from "react";
import { IAttachmentRequest } from "../../api/Attachments";

interface IWidget {
  data: IWbw;
  onUpload?: Function;
  onChange?: Function;
  onDialogOpen?: Function;
}
const WbwDetailUploadWidget = ({
  data,
  onUpload,
  onChange,
  onDialogOpen,
}: IWidget) => {
  const intl = useIntl();
  const user = useAppSelector(currentUser);
  const attachments = data.attachments;

  const props: UploadProps = {
    name: "file",
    action: `${API_HOST}/api/v2/attachment`,
    headers: {
      Authorization: `Bearer ${getToken()}`,
    },
    defaultFileList: data.attachments?.map((item) => {
      return {
        uid: item.id,
        name: item.title ? item.title : "",
      };
    }),
    onChange(info) {
      console.log("onchange", info);
      if (typeof onUpload !== "undefined") {
        onUpload(info.fileList);
      }
      if (info.file.status !== "uploading") {
        console.log(info.file, info.fileList);
      }
      if (info.file.status === "done") {
        message.success(`${info.file.name} file uploaded successfully`);
        console.log("file info", info);
      } else if (info.file.status === "error") {
        message.error(`${info.file.name} file upload failed.`);
      }
    },
    onRemove(file) {
      console.log("remove", file);
    },
  };

  /**
   *       <Upload {...props}>
        <Button icon={<UploadOutlined />}>
          {intl.formatMessage({ id: "buttons.click.upload" })}
        </Button>
      </Upload>
   */
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
              if (typeof onUpload !== "undefined") {
                onUpload([value]);
              }
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
                      (value: IWbwAttachment, index: number) => index !== id
                    );
                    if (typeof onChange !== "undefined") {
                      onChange(output);
                    }
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
