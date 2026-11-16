import { ProFormSelect } from "@ant-design/pro-components";
import { useIntl } from "react-intl";

import { searchGroups } from "../../api/group";

interface IWidget {
  name?: string;
  width?: number | "md" | "sm" | "xl" | "xs" | "lg";
  multiple?: boolean;
  hidden?: boolean;
}
const GroupSelectWidget = ({
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
        // 只搜得到公开团队
        const groups = await searchGroups(keyWords ?? "");
        return groups.map((item) => ({
          value: item.id,
          label: `${item.owner?.userName ?? ""}/${item.name}`,
        }));
      }}
      rules={[
        {
          required: true,
        },
      ]}
    />
  );
};

export default GroupSelectWidget;
