import { ProFormSelect, RequestOptionsType } from "@ant-design/pro-components";
import { useIntl } from "react-intl";

import { get } from "../../request";
import { IUserListResponse } from "../api/Auth";

interface IWidget {
  name?: string;
  width?: number | "md" | "sm" | "xl" | "xs" | "lg";
  multiple?: boolean;
  hidden?: boolean;
  hiddenTitle?: boolean;
  required?: boolean;
  initialValue?: string | string[] | null;
  options?: RequestOptionsType[];
}
const UserSelectWidget = ({
  name = "user",
  multiple = false,
  width = "md",
  hidden = false,
  hiddenTitle = false,
  required = true,
  options = [],
  initialValue,
}: IWidget) => {
  const intl = useIntl();
  console.log("UserSelect options", options);
  return (
    <ProFormSelect
      name={name}
      label={
        hiddenTitle
          ? undefined
          : intl.formatMessage({ id: "forms.fields.user.label" })
      }
      hidden={hidden}
      width={width}
      initialValue={initialValue}
      showSearch
      debounceTime={300}
      fieldProps={{
        mode: multiple ? "tags" : undefined,
      }}
      request={async ({ keyWords }) => {
        console.log("keyWord", keyWords);

        if (typeof keyWords === "string") {
          const json = await get<IUserListResponse>(
            `/v2/user?view=key&key=${keyWords}`
          );
          console.info("api response user select", json);
          const userList: RequestOptionsType[] = json.data.rows.map((item) => {
            return {
              value: item.id,
              label: `${item.nickName}`,
            };
          });
          console.log("json", userList);
          return userList;
        } else {
          const defaultOptions: RequestOptionsType[] = options.map((item) => {
            return { label: item.label, value: item.value?.toString() };
          });
          return defaultOptions;
        }
      }}
      rules={[
        {
          required: required,
        },
      ]}
    />
  );
};

export default UserSelectWidget;
