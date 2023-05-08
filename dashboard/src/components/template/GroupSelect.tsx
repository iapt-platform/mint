import { ProFormSelect } from "@ant-design/pro-components";
import { useIntl } from "react-intl";

import { get } from "../../request";
import { IGroupListResponse } from "../api/Group";

interface IWidget {
  name?: string;
  width?: number | "md" | "sm" | "xl" | "xs" | "lg";
  multiple?: boolean;
  hidden?: boolean;
}
const Widget = ({
  name = "user",
  multiple = false,
  width = "md",
  hidden = false,
}: IWidget) => {
  const intl = useIntl();
  return (
    <ProFormSelect
      name={name}
      label={intl.formatMessage({ id: "group.fields.name.label" })}
      hidden={hidden}
      width={width}
      showSearch
      debounceTime={300}
      fieldProps={{
        mode: multiple ? "multiple" : undefined,
      }}
      request={async ({ keyWords }) => {
        console.log("group keyWord", keyWords);
        const json = await get<IGroupListResponse>(
          `/v2/group?view=key&key=${keyWords}`
        );
        console.log("json", json);
        const userList = json.data.rows.map((item) => {
          return {
            value: item.uid,
            label: `${item.studio.studioName}/${item.name}`,
          };
        });

        return userList;
      }}
      rules={[
        {
          required: true,
        },
      ]}
    />
  );
};

export default Widget;
