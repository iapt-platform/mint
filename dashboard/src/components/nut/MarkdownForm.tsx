import { useState } from "react";
import { useIntl } from "react-intl";
import { ProForm, ProFormTextArea } from "@ant-design/pro-components";
import { message } from "antd";
import MDEditor from "@uiw/react-md-editor";

interface IFormData {
  plain: string;
}
const Widget = () => {
  const intl = useIntl();
  const [mdValue, setMdValue] = useState("**Hello mint!!!**");

  return (
    <ProForm<IFormData>
      onFinish={async (values: IFormData) => {
        // TODO
        console.log(mdValue, values);
        message.success(intl.formatMessage({ id: "flashes.success" }));
      }}
    >
      <ProForm.Group>
        <ProFormTextArea
          width="md"
          name="plain"
          required
          label="Plain text"
          rules={[{ required: true }]}
        />
      </ProForm.Group>
      <ProForm.Group>
        <MDEditor
          value={mdValue}
          onChange={(value: string | undefined) => {
            if (value) {
              setMdValue(value);
            }
          }}
        />
      </ProForm.Group>
    </ProForm>
  );
};

export default Widget;
