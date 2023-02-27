import { ProFormSelect } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
interface IWidget {
  width?: number | "md" | "sm" | "xl" | "xs" | "lg";
}
const Widget = ({ width }: IWidget) => {
  const intl = useIntl();

  const options = [
    {
      value: 0,
      label: intl.formatMessage({
        id: "forms.fields.publicity.disable.label",
      }),
    },
    {
      value: 10,
      label: intl.formatMessage({
        id: "forms.fields.publicity.private.label",
      }),
    },
    {
      value: 30,
      label: intl.formatMessage({
        id: "forms.fields.publicity.public.label",
      }),
    },
  ];
  return (
    <ProFormSelect
      options={options}
      width={width}
      name="status"
      allowClear={false}
      label={intl.formatMessage({ id: "forms.fields.publicity.label" })}
    />
  );
};

export default Widget;
