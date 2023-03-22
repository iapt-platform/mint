import { ModalForm, ProFormUploadDragger } from "@ant-design/pro-components";
import { Form, message } from "antd";

import { API_HOST, get } from "../../../request";
import { UploadFile } from "antd/es/upload/interface";
import { IAttachmentResponse } from "../../api/Attachments";

interface INissayaEndingUpload {
  filename: UploadFile<IAttachmentResponse>[];
}
export interface INissayaEndingImportResponse {
  ok: boolean;
  message: string;
  data: number;
}

interface IWidget {
  url: string;
  urlExtra?: string;
  trigger?: JSX.Element;
  onSuccess?: Function;
}
const Widget = ({
  url,
  urlExtra,
  trigger = <>{"trigger"}</>,
  onSuccess,
}: IWidget) => {
  const [form] = Form.useForm<INissayaEndingUpload>();

  return (
    <ModalForm<INissayaEndingUpload>
      title="upload"
      trigger={trigger}
      form={form}
      autoFocusFirstInput
      modalProps={{
        destroyOnClose: true,
        onCancel: () => console.log("run"),
      }}
      submitTimeout={2000}
      onFinish={async (values) => {
        console.log(values);
        let _filename: string = "";

        if (
          typeof values.filename === "undefined" ||
          values.filename.length === 0
        ) {
          _filename = "";
        } else if (typeof values.filename[0].response === "undefined") {
          _filename = values.filename[0].uid;
        } else {
          _filename = values.filename[0].response.data.url;
        }

        const queryUrl = `${url}?filename=${_filename}&${urlExtra}`;
        console.log("url", queryUrl);
        const res = await get<INissayaEndingImportResponse>(queryUrl);

        console.log(res);
        if (res.ok) {
          message.success(`导入数据${res.data}`);
          if (typeof onSuccess !== "undefined") {
            onSuccess();
          }
        } else {
          message.error(res.message);
        }

        return true;
      }}
    >
      <ProFormUploadDragger
        max={1}
        label="上传xlsx"
        name="filename"
        fieldProps={{
          name: "file",
        }}
        action={`${API_HOST}/api/v2/attachments`}
      />
    </ModalForm>
  );
};

export default Widget;
