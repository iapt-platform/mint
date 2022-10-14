import { ProForm, ProFormText, ProFormSelect } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { message } from "antd";

interface IFormData {
	title: string;
	lang: string;
}

type IWidgetArticleCreate = {
	studio: string | undefined;
};
const Widget = (param: IWidgetArticleCreate) => {
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
					name="title"
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
							value: "zh-Hans",
							label: intl.formatMessage({ id: "languages.zh-Hans" }),
						},
						{
							value: "en-US",
							label: intl.formatMessage({ id: "English" }),
						},
					]}
					width="md"
					name="lang"
					label={intl.formatMessage({ id: "forms.fields.lang.label" })}
				/>
			</ProForm.Group>
		</ProForm>
	);
};

export default Widget;
