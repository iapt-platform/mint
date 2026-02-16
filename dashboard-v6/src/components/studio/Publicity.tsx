import { ProFormSelect } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { publicityList, TPublicity } from "./PublicitySelect";

interface IWidget {
  width?: number | "md" | "sm" | "xl" | "xs" | "lg";
  disable?: TPublicity[];
  name?: string;
  readonly?: boolean;
}
const Publicity = ({
  width,
  disable = [],
  name = "status",
  readonly,
}: IWidget) => {
  const intl = useIntl();

  const options = publicityList.map((item) => {
    return {
      value: item,
      label: intl.formatMessage({
        id: `forms.fields.publicity.${item}.label`,
      }),
      disable: disable.includes(item),
    };
  });

  return (
    <ProFormSelect
      options={options.filter((value) => value.disable === false)}
      readonly={readonly}
      width={width}
      name={name}
      allowClear={false}
      label={intl.formatMessage({ id: "forms.fields.publicity.label" })}
    />
  );
};

export default Publicity;
