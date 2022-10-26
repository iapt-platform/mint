import { useIntl } from "react-intl";
import { ProForm, ProFormText } from "@ant-design/pro-components";
import { message } from "antd";
import { useNavigate } from "react-router-dom";

import { setTitle } from "../../../reducers/layout";
import { useAppSelector, useAppDispatch } from "../../../hooks";
import { signIn, TO_PROFILE } from "../../../reducers/current-user";

interface IFormData {
  email: string;
  password: string;
}
const Widget = () => {
  const intl = useIntl();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();

  return (
    <ProForm<IFormData>
      onFinish={async (values: IFormData) => {
        // TODO
        console.log(values);
        // dispatch(signIn([user, token]));
        // navigate(TO_PROFILE);
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
