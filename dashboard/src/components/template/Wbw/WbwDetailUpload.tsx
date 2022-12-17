import { useIntl } from "react-intl";
import { UploadOutlined } from "@ant-design/icons";
import type { UploadProps } from "antd";
import { Button, message, Upload } from "antd";
import { API_HOST } from "../../../request";
import { IWbw } from "./WbwWord";

interface IWidget {
  data: IWbw;
  onChange?: Function;
}
const Widget = ({ data, onChange }: IWidget) => {
  const intl = useIntl();

  const props: UploadProps = {
    name: "file",
    action: `${API_HOST}/api/v2/upload`,
    headers: {
      authorization: "authorization-text",
    },
    defaultFileList: data.attachments,
    onChange(info) {
      if (info.file.status !== "uploading") {
        console.log(info.file, info.fileList);
      }
      if (info.file.status === "done") {
        message.success(`${info.file.name} file uploaded successfully`);
      } else if (info.file.status === "error") {
        message.error(`${info.file.name} file upload failed.`);
      }
    },
  };

  return (
    <Upload {...props}>
      <Button icon={<UploadOutlined />}>
        {intl.formatMessage({ id: "buttons.click.upload" })}
      </Button>
    </Upload>
  );
};

export default Widget;
