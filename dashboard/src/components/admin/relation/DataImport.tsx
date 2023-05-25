import { ModalForm, ProFormUploadDragger } from "@ant-design/pro-components";
import { Form, message } from "antd";

import { API_HOST, get } from "../../../request";
import { UploadFile } from "antd/es/upload/interface";
import { IAttachmentResponse } from "../../api/Attachments";
import modal from "antd/lib/modal";

interface INissayaEndingUpload {
  filename: UploadFile<IAttachmentResponse>[];
}
export interface INissayaEndingImportResponse {
  ok: boolean;
  message: string;
  data: {
    success: number;
    fail: number;
  };
}

interface IWidget {
  title?: string;
  url: string;
  urlExtra?: string;
  trigger?: JSX.Element;
  onSuccess?: Function;
}
const DataImportWidget = ({
  title = "upload",
  url,
  urlExtra,
  trigger = <>{"trigger"}</>,
  onSuccess,
}: IWidget) => {
  const [form] = Form.useForm<INissayaEndingUpload>();

  return (
    <ModalForm<INissayaEndingUpload>
      title={title}
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

        console.log("import", res);
        if (res.ok) {
          if (res.data.fail > 0) {
            modal.info({
              title: "error",
              content: `成功${res.data.success}-失败${res.data.fail}\n${res.message}`,
            });
          } else {
            message.success(`成功导入${res.data.success}`);
          }

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
        label="请确保您的xlsx文件是用导出功能导出的。word为空可以删除该词条。使用其他studio导出的数据，请将channel_id设置为空。否则该术语将被忽略。"
        name="filename"
        fieldProps={{
          name: "file",
        }}
        action={`${API_HOST}/api/v2/attachments`}
      />
    </ModalForm>
  );
};

export default DataImportWidget;
