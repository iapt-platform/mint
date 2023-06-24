import { ProFormSelect } from "@ant-design/pro-components";
import { useIntl } from "react-intl";

import { get } from "../../request";
import { IUserListResponse } from "../api/Auth";

interface IWidget {
  name?: string;
  width?: number | "md" | "sm" | "xl" | "xs" | "lg";
  multiple?: boolean;
  hidden?: boolean;
}
const UserSelectWidget = ({
  name = "user",
  multiple = false,
  width = "md",
  hidden = false,
}: IWidget) => {
  const intl = useIntl();
  return (
    <ProFormSelect
      name={name}
      label={intl.formatMessage({ id: "forms.fields.user.label" })}
      hidden={hidden}
      width={width}
      showSearch
      debounceTime={300}
      fieldProps={{
        mode: multiple ? "multiple" : undefined,
      }}
      request={async ({ keyWords }) => {
        console.log("keyWord", keyWords);

        if (typeof keyWords === "string") {
          const json = await get<IUserListResponse>(
            `/v2/user?view=key&key=${keyWords}`
          );
          const userList = json.data.rows.map((item) => {
            return {
              value: item.id,
              label: `${item.userName}-${item.nickName}`,
            };
          });
          console.log("json", userList);
          return userList;
        } else {
          return [];
        }
      }}
      rules={[
        {
          required: true,
        },
      ]}
    />
  );
};

export default UserSelectWidget;
