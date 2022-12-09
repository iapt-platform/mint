import { useIntl } from "react-intl";
import { ProForm, ProFormText } from "@ant-design/pro-components";
import { message } from "antd";

import LangSelect from "../general/LangSelect";
import { post } from "../../request";
import { IArticleCreateRequest, IArticleResponse } from "../api/Article";

interface IFormData {
  title: string;
  lang: string;
  studio: string;
}

type IWidgetArticleCreate = {
  studio?: string;
};
const Widget = (prop: IWidgetArticleCreate) => {
  const intl = useIntl();

  return (
    <ProForm<IFormData>
      onFinish={async (values: IFormData) => {
        console.log(values);
        values.studio = prop.studio ? prop.studio : "";
        const res = await post<IArticleCreateRequest, IArticleResponse>(
          `/v2/article`,
          values
        );
        console.log(res);
        if (res.ok) {
          message.success(intl.formatMessage({ id: "flashes.success" }));
        } else {
          message.error(res.message);
        }
      }}
    >
      <ProForm.Group>
        <ProFormText
          width="md"
          name="title"
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
      </ProForm.Group>
      <ProForm.Group>
        <LangSelect />
      </ProForm.Group>
    </ProForm>
  );
};

export default Widget;
