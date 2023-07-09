import { useIntl } from "react-intl";
import { UploadOutlined } from "@ant-design/icons";
import type { UploadProps } from "antd";
import { Button, message, Upload } from "antd";

import { API_HOST } from "../../../request";
import { get as getToken } from "../../../reducers/current-user";
import { IWbw } from "./WbwWord";

interface IWidget {
  data: IWbw;
  onUpload?: Function;
}
const WbwDetailUploadWidget = ({ data, onUpload }: IWidget) => {
  const intl = useIntl();

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

  return (
    <Upload {...props}>
      <Button icon={<UploadOutlined />}>
        {intl.formatMessage({ id: "buttons.click.upload" })}
      </Button>
    </Upload>
  );
};

export default WbwDetailUploadWidget;
