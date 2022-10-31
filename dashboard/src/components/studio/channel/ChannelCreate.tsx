import { ProForm, ProFormText, ProFormSelect } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { message } from "antd";

interface IFormData {
	name: string;
	type: string;
}

type IWidgetChannelCreate = {
	studio: string | undefined;
};
const Widget = (param: IWidgetChannelCreate) => {
	const intl = useIntl();

	return (
		<ProForm<IFormData>
			onFinish={async (values: IFormData) => {
				// TODO
				console.log(values);
				message.success(intl.formatMessage({ id: "flashes.success" }));
			}}
		>
			<ProForm.Group>
				<ProFormText
					width="md"
					name="name"
					required
					label={intl.formatMessage({ id: "channel.name" })}
					rules={[
						{
							required: true,
							message: intl.formatMessage({ id: "channel.create.message.noname" }),
						},
					]}
				/>
			</ProForm.Group>
			<ProForm.Group>
				<ProFormSelect
					options={[
						{
							value: "translation",
							label: intl.formatMessage({ id: "channel.type.translation.title" }),
						},
						{
							value: "nissaya",
							label: intl.formatMessage({ id: "channel.type.nissaya.title" }),
						},
					]}
					width="md"
					name="type"
					label={intl.formatMessage({ id: "channel.type" })}
				/>
			</ProForm.Group>
		</ProForm>
	);
};

export default Widget;
