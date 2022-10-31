import { ProForm, ProFormText, ProFormTextArea } from "@ant-design/pro-components";
import { Layout } from "antd";
import { useIntl } from "react-intl";
import { message } from "antd";

import SelectLang from "../SelectLang";
import SelectCase from "../SelectCase";
import Confidene from "../Confidence";

interface IFormData {
	word: string;
	type: string;
	grammar: string;
	parent: string;
	meaning: string;
	note: string;
	factors: string;
	factormeaning: string;
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

	const onLangSearch = (value: string) => {
		console.log("search:", value);
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
					<div>语法信息</div>
					<SelectCase />
				</ProForm.Group>
				<ProForm.Group>
					<ProFormText
						width="md"
						name="parent"
						label={intl.formatMessage({ id: "dict.fields.parent.label" })}
					/>
				</ProForm.Group>
				<ProForm.Group>
					<div>语言</div>
					<SelectLang />
				</ProForm.Group>
				<ProForm.Group>
					<ProFormText
						width="md"
						name="meaning"
						label={intl.formatMessage({ id: "dict.fields.meaning.label" })}
					/>
				</ProForm.Group>
				<ProForm.Group>
					<ProFormText
						width="md"
						name="factors"
						label={intl.formatMessage({ id: "dict.fields.factors.label" })}
					/>
				</ProForm.Group>
				<ProForm.Group>
					<ProFormText
						width="md"
						name="factormeaning"
						label={intl.formatMessage({ id: "dict.fields.factormeaning.label" })}
					/>
				</ProForm.Group>
				<ProForm.Group>
					<ProFormTextArea name="note" label={intl.formatMessage({ id: "forms.fields.note.label" })} />
				</ProForm.Group>
				<Layout>
					<div>信心指数</div>
					<Confidene />
				</Layout>
			</ProForm>
		</Layout>
	);
};

export default Widget;
