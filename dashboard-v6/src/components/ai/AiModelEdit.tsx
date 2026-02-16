import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormInstance,
  ProFormText,
  ProFormTextArea,
} from "@ant-design/pro-components";
import { message } from "antd";
import { get, put } from "../../request";
import { useRef } from "react";
import { IAiModelRequest, IAiModelResponse } from "../api/ai";
import Publicity from "../studio/Publicity";

interface IWidget {
  studioName?: string;
  modelId?: string;
  onChange?: Function;
}
const AiModelEdit = ({ studioName, modelId, onChange }: IWidget) => {
  const intl = useIntl();
  const formRef = useRef<ProFormInstance>();

  return (
    <ProForm<IAiModelRequest>
      formRef={formRef}
      onFinish={async (values: IAiModelRequest) => {
        if (typeof studioName === "undefined") {
          return;
        }
        const url = `/v2/ai-model/${modelId}`;
        console.info("api request", url, values);
        const res = await put<IAiModelRequest, IAiModelResponse>(url, values);
        console.info("api response", res);
        if (res.ok) {
          message.success(intl.formatMessage({ id: "flashes.success" }));
          onChange && onChange();
        } else {
          message.error(res.message);
        }
      }}
      request={async () => {
        const url = `/v2/ai-model/${modelId}`;
        console.info("api request", url);
        const res = await get<IAiModelResponse>(url);
        console.info("api response", res);
        return res.data;
      }}
    >
      <ProForm.Group>
        <ProFormText
          width="md"
          name="name"
          required
          label={intl.formatMessage({ id: "forms.fields.name.label" })}
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
      <ProForm.Group>
        <ProFormText
          width="md"
          name="url"
          label={intl.formatMessage({ id: "forms.fields.url.label" })}
        />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormText
          width="md"
          name="model"
          label={intl.formatMessage({ id: "forms.fields.model.label" })}
        />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormText
          width="md"
          name="key"
          label={intl.formatMessage({ id: "forms.fields.key.label" })}
        />
      </ProForm.Group>
      <ProForm.Group>
        <Publicity name="privacy" disable={["public_no_list", "blocked"]} />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormTextArea
          width="md"
          name="description"
          label={intl.formatMessage({ id: "forms.fields.description.label" })}
        />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormTextArea
          width="md"
          name="system_prompt"
          label={"system_prompt"}
        />
      </ProForm.Group>
    </ProForm>
  );
};

export default AiModelEdit;
