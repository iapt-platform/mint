import { useIntl } from "react-intl";
import { ProForm, ProFormText } from "@ant-design/pro-components";
import { message } from "antd";

interface IFormData {
  email: string;
  password: string;
}
const Widget = () => {
  const intl = useIntl();

  return (
    <ProForm<IFormData>
      onFinish={async (values: IFormData) => {
        // TODO
        console.log(values);
        message.success(intl.formatMessage({ id: "flashes.success" }));
      }}
    >
      <ProForm.Group>
        <ProFormText
          width="md"
          name="email"
          required
          label={intl.formatMessage({ id: "forms.fields.email.label" })}
          rules={[{ required: true, type: "email", max: 255, min: 6 }]}
        />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormText
          width="md"
          name="password"
          required
          label={intl.formatMessage({ id: "forms.fields.password.label" })}
          rules={[{ required: true, max: 32, min: 8 }]}
        />
      </ProForm.Group>
    </ProForm>
  );
};

export default Widget;
