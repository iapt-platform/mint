import { ProForm, ProFormText, ProFormTextArea } from "@ant-design/pro-components";
import { Layout } from "antd";
import { useIntl } from "react-intl";
import { message } from "antd";

import SelectLang from "../SelectLang";

interface IFormData {
	word: string;
	tag: string;
	meaning: string;
	meaning2: string;
	note: string;
	channel: string;
	lang: string;
}

type IWidgetDictCreate = {
	studio: string | undefined;
};
const Widget = (param: IWidgetDictCreate) => {
	const intl = useIntl();
	/*
	const onLangChange = (value: string) => {
		console.log(`selected ${value}`);
	};
*/
	return (
		<Layout>
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
						name="word"
						required
						label={intl.formatMessage({ id: "dict.fields.word.label" })}
						rules={[
							{
								required: true,
								message: intl.formatMessage({ id: "channel.create.message.noname" }),
							},
						]}
					/>
				</ProForm.Group>
				<ProForm.Group>
					<ProFormText
						width="md"
						name="tag"
						tooltip={intl.formatMessage({ id: "term.fields.description.tooltip" })}
						label={intl.formatMessage({ id: "term.fields.description.label" })}
					/>
				</ProForm.Group>
				<ProForm.Group>
					<ProFormText
						width="md"
						name="meaning"
						tooltip={intl.formatMessage({ id: "term.fields.meaning.tooltip" })}
						label={intl.formatMessage({ id: "term.fields.meaning.label" })}
					/>
				</ProForm.Group>
				<ProForm.Group>
					<ProFormText
						width="md"
						name="meaning2"
						tooltip={intl.formatMessage({ id: "term.fields.meaning2.tooltip" })}
						label={intl.formatMessage({ id: "term.fields.meaning2.label" })}
					/>
				</ProForm.Group>
				<ProForm.Group>
					<ProFormText
						width="md"
						name="channel"
						tooltip={intl.formatMessage({ id: "term.fields.channel.tooltip" })}
						label={intl.formatMessage({ id: "term.fields.channel.label" })}
					/>
				</ProForm.Group>
				<ProForm.Group>
					<div>语言</div>
					<SelectLang />
				</ProForm.Group>
				<ProForm.Group>
					<ProFormTextArea name="note" label={intl.formatMessage({ id: "forms.fields.note.label" })} />
				</ProForm.Group>
			</ProForm>
		</Layout>
	);
};

export default Widget;
