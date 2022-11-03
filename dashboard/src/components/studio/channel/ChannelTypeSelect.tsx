import { ProFormSelect } from "@ant-design/pro-components";
import { useIntl } from "react-intl";

const Widget = () => {
	const intl = useIntl();

	const channelTypeOptions = [
		{
			value: "translation",
			label: intl.formatMessage({ id: "channel.type.translation.title" }),
		},
		{
			value: "nissaya",
			label: intl.formatMessage({ id: "channel.type.nissaya.title" }),
		},
		{
			value: "commentary",
			label: intl.formatMessage({ id: "channel.type.commentary.title" }),
		},
		{
			value: "original",
			label: intl.formatMessage({ id: "channel.type.original.title" }),
		},
		{
			value: "general",
			label: intl.formatMessage({ id: "channel.type.general.title" }),
		},
	];
	return (
		<ProFormSelect
			options={channelTypeOptions}
			initialValue="translation"
			width="xs"
			name="type"
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

export default Widget;
