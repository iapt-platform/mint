import { Select } from "antd";
import { useIntl } from "react-intl";

interface IWidget {
  onSelect?: Function;
}
const ChapterFilterTypeWidget = ({ onSelect }: IWidget) => {
  const intl = useIntl();

  return (
    <Select
      style={{ minWidth: 100 }}
      placeholder="Type"
      defaultValue={["translation"]}
      onChange={(value: string[]) => {
        console.log(`selected ${value}`);
        if (typeof onSelect !== "undefined") {
          onSelect(value);
        }
      }}
      options={[
        {
          value: "translation",
          label: intl.formatMessage({ id: "channel.type.translation.label" }),
        },
        {
          value: "nissaya",
          label: intl.formatMessage({ id: "channel.type.nissaya.label" }),
        },
        {
          value: "commentary",
          label: intl.formatMessage({ id: "channel.type.commentary.label" }),
        },
        {
          value: "original",
          label: intl.formatMessage({ id: "channel.type.original.label" }),
        },
      ]}
    />
  );
};

export default ChapterFilterTypeWidget;
