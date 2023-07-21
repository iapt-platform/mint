import { useIntl } from "react-intl";
import { ProFormSelect } from "@ant-design/pro-components";

export const LangValueEnum = () => {
  const intl = useIntl();
  return {
    all: {
      text: intl.formatMessage({
        id: "tables.publicity.all",
      }),
      status: "Default",
    },
    en: {
      text: "English",
    },
    "zh-Hans": { text: "简体中文" },
    "zh-Hant": {
      text: "繁体中文",
    },
    my: {
      text: "缅文",
    },
  };
};

interface IWidget {
  width?: number | "md" | "sm" | "xl" | "xs" | "lg";
  label?: string;
}
const LangSelectWidget = ({ width, label }: IWidget) => {
  const intl = useIntl();

  const langOptions = [
    {
      value: "en-US",
      label: "English",
    },
    {
      value: "zh-Hans",
      label: "简体中文 zh-Hans",
    },
    {
      value: "zh-Hant",
      label: "繁体中文 zh-Hant",
    },
    {
      value: "my",
      label: "缅文 my",
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
      label={
        label ? label : intl.formatMessage({ id: "forms.fields.lang.label" })
      }
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

export default LangSelectWidget;
