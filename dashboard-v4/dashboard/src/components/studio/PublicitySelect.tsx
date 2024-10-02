import { ProFormSelect } from "@ant-design/pro-components";
import { useIntl } from "react-intl";

export type TPublicity =
  | "disable"
  | "blocked"
  | "private"
  | "public_no_list"
  | "public";

interface IWidget {
  width?: number | "md" | "sm" | "xl" | "xs" | "lg";
  disable?: TPublicity[];
  readonly?: boolean;
}
const PublicitySelectWidget = ({ width, disable = [], readonly }: IWidget) => {
  const intl = useIntl();

  const options = [
    {
      value: 0,
      label: intl.formatMessage({
        id: "forms.fields.publicity.disable.label",
      }),
      disable: disable.includes("disable"),
    },
    {
      value: 5,
      label: intl.formatMessage({
        id: "forms.fields.publicity.blocked.label",
      }),
      disable: true,
    },
    {
      value: 10,
      label: intl.formatMessage({
        id: "forms.fields.publicity.private.label",
      }),
      disable: disable.includes("private"),
    },
    {
      value: 20,
      label: intl.formatMessage({
        id: "forms.fields.publicity.public_no_list.label",
      }),
      disable: disable.includes("public_no_list"),
    },
    {
      value: 30,
      label: intl.formatMessage({
        id: "forms.fields.publicity.public.label",
      }),
      disable: disable.includes("public"),
    },
  ];

  console.debug("disable", disable, options);
  return (
    <ProFormSelect
      options={options.filter((value) => value.disable === false)}
      readonly={readonly}
      width={width}
      name="status"
      allowClear={false}
      label={intl.formatMessage({ id: "forms.fields.publicity.label" })}
    />
  );
};

export default PublicitySelectWidget;
