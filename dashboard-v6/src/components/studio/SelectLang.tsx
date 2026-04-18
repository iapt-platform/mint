import { Select } from "antd";
import { useIntl } from "react-intl";

const { Option } = Select;

const onLangChange = (value: string) => {
  console.log(`selected ${value}`);
};

const onLangSearch = (value: string) => {
  console.log("search:", value);
};

interface IWidgetSelectLang {
  lang?: string;
}
const SelectLangWidget = (prop: IWidgetSelectLang) => {
  const intl = useIntl();

  const data = [
    { value: "en", label: intl.formatMessage({ id: "languages.en-US" }) },
    {
      value: "zh-Hans",
      label: intl.formatMessage({ id: "languages.zh-Hans" }),
    },
    {
      value: "zh-Hant",
      label: intl.formatMessage({ id: "languages.zh-Hant" }),
    },
    {
      value: "zh",
      label: intl.formatMessage({ id: "languages.zh" }),
    },
  ];
  const langOptions = data.map((d, id) => (
    <Option key={id} value={d.value}>
      {d.label}
    </Option>
  ));
  return (
    <Select
      showSearch
      placeholder="Select a language"
      optionFilterProp="children"
      onChange={onLangChange}
      onSearch={onLangSearch}
      value={prop.lang ? prop.lang : ""}
      filterOption={(input, option) =>
        (option!.children as unknown as string)
          .toLowerCase()
          .includes(input.toLowerCase())
      }
    >
      {langOptions}
    </Select>
  );
};

export default SelectLangWidget;
