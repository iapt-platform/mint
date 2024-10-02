import {
  ProForm,
  ProFormSelect,
  ProFormText,
} from "@ant-design/pro-components";
import { message } from "antd";

import { get, put } from "../../request";
import { IUserApiData, IUserRequest, IUserResponse } from "../api/Auth";

import { useIntl } from "react-intl";

interface IWidget {
  userId?: string;
  onLoad?: Function;
}

const AccountWidget = ({ userId, onLoad }: IWidget) => {
  const intl = useIntl();

  return (
    <ProForm<IUserApiData>
      onFinish={async (values: IUserApiData) => {
        console.log(values);

        const url = `/v2/user/${userId}`;
        const postData = {
          roles: values.role,
        };
        console.log("account put ", url, postData);
        const res = await put<IUserRequest, IUserResponse>(url, postData);

        if (res.ok) {
          message.success(intl.formatMessage({ id: "flashes.success" }));
        } else {
          message.error(res.message);
        }
      }}
      params={{}}
      request={async () => {
        const url = `/v2/user/${userId}`;
        console.log("url", url);
        const res = await get<IUserResponse>(url);
        if (res.ok) {
          if (typeof onLoad !== "undefined") {
            onLoad(res.data);
          }
        }
        return res.data;
      }}
    >
      <ProFormText width="md" readonly name="userName" label="User Name" />
      <ProFormText width="md" readonly name="nickName" label="Nick Name" />
      <ProFormText width="md" readonly name="email" label="Email" />
      <ProFormSelect
        options={["administrator", "member", "uploader", "basic"].map(
          (item) => {
            return {
              value: item,
              label: item,
            };
          }
        )}
        fieldProps={{
          mode: "tags",
        }}
        width="md"
        name="role"
        allowClear={true}
        label={intl.formatMessage({ id: "forms.fields.role.label" })}
      />
    </ProForm>
  );
};

export default AccountWidget;
