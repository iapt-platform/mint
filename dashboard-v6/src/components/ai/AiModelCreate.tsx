import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormInstance,
  ProFormText,
} from "@ant-design/pro-components";
import { message } from "antd";
import { post } from "../../request";
import { useRef } from "react";
import { IAiModelRequest, IAiModelResponse } from "../api/ai";

interface IWidget {
  studioName?: string;
  onCreate?: Function;
}
const AiModelCreate = ({ studioName, onCreate }: IWidget) => {
  const intl = useIntl();
  const formRef = useRef<ProFormInstance>();

  return (
    <ProForm<IAiModelRequest>
      formRef={formRef}
      onFinish={async (values: IAiModelRequest) => {
        if (typeof studioName === "undefined") {
          return;
        }
        const url = `/v2/ai-model`;
        console.info("api request", url, values);
        const res = await post<IAiModelRequest, IAiModelResponse>(url, values);
        console.info("api response", res);
        if (res.ok) {
          message.success(intl.formatMessage({ id: "flashes.success" }));
          if (typeof onCreate !== "undefined") {
            onCreate();
            formRef.current?.resetFields();
          }
        } else {
          message.error(res.message);
        }
      }}
    >
      <ProForm.Group>
        <ProFormText
          width="md"
          name="name"
          required
          label={intl.formatMessage({ id: "channel.name" })}
          rules={[
            {
              required: true,
              message: intl.formatMessage({
                id: "channel.create.message.noname",
              }),
            },
          ]}
        />
        <ProFormText
          width="md"
          name="studio_name"
          initialValue={studioName}
          hidden
          required
        />
      </ProForm.Group>
    </ProForm>
  );
};

export default AiModelCreate;
