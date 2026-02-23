import { useIntl } from "react-intl";
import { ProFormSelect } from "@ant-design/pro-components";

interface IWidget {
  readonly?: boolean;
}

const ChannelTypeSelectWidget = ({ readonly }: IWidget) => {
  const intl = useIntl();

  const channelTypeOptions = [
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
    {
      value: "similar",
      label: intl.formatMessage({ id: "channel.type.similar.label" }),
    },
  ];
  return (
    <ProFormSelect
      options={channelTypeOptions}
      initialValue="translation"
      width="xs"
      name="type"
      readonly={readonly}
      allowClear={false}
      label={intl.formatMessage({ id: "channel.type" })}
      rules={[
        {
          required: true,
          message: intl.formatMessage({
            id: "channel.type.message.required",
          }),
        },
      ]}
    />
  );
};

export default ChannelTypeSelectWidget;
