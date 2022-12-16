import { useIntl } from "react-intl";
import { ProForm, ProFormText } from "@ant-design/pro-components";
import { message } from "antd";

interface IFormData {
  name: string;
}

type IWidgetGroupCreate = {
  studio: string | undefined;
};
const Widget = (param: IWidgetGroupCreate) => {
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
      </ProForm.Group>
    </ProForm>
  );
};

export default Widget;
