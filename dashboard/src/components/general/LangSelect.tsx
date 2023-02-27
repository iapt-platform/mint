import { useIntl } from "react-intl";
import { ProFormSelect } from "@ant-design/pro-components";

interface IWidget {
  width?: number | "md" | "sm" | "xl" | "xs" | "lg";
}
const Widget = ({ width }: IWidget) => {
  const intl = useIntl();

  const langOptions = [
    {
      value: "English",
      label: "en-US",
    },
    {
      value: "zh-Hans",
      label: "简体中文 zh-Hans",
    },
    {
      value: "zh-Hant",
      label: "繁体中文 zh-Hant",
    },
  ];
  return (
    <ProFormSelect
      options={langOptions}
      width={width}
      name="lang"
      showSearch
      debounceTime={300}
      allowClear={false}
      label={intl.formatMessage({ id: "forms.fields.lang.label" })}
      rules={[
        {
          required: true,
          message: intl.formatMessage({
            id: "forms.message.lang.required",
          }),
        },
      ]}
    />
  );
};

export default Widget;
