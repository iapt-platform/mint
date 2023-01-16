import { useIntl } from "react-intl";
import { ProForm, ProFormText } from "@ant-design/pro-components";
import { message } from "antd";

import { post } from "../../../request";
import { ICourseCreateRequest, ICourseResponse } from "../../api/Course";
import LangSelect from "../../general/LangSelect";

interface IFormData {
  title: string;
  lang: string;
  studio: string;
}

type IWidgetCourseCreate = {
  studio?: string;
};
const Widget = (prop: IWidgetCourseCreate) => {
  const intl = useIntl();

  return (
    <ProForm<IFormData>
      onFinish={async (values: IFormData) => {
        console.log(values);
        values.studio = prop.studio ? prop.studio : "";
        const res = await post<ICourseCreateRequest, ICourseResponse>(
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
