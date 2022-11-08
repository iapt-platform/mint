import { useIntl } from "react-intl";
import { Button, Form, message } from "antd";
import {
	ModalForm,
	ProForm,
	ProFormText,
	ProFormTextArea,
} from "@ant-design/pro-components";
import { PlusOutlined } from "@ant-design/icons";

import SelectLang from "../SelectLang";
import { ITermResponse } from "../../api/Term";
import { get } from "../../../request";
import { useState } from "react";

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
	studio?: string;
	edit?: boolean;
	wordId?: string;
	word?: string;
};
const Widget = (prop: IWidgetDictCreate) => {
	const intl = useIntl();
	const [form] = Form.useForm<IFormData>();
	const [lang, setLang] = useState("");

	const waitTime = (time: number = 100) => {
		return new Promise((resolve) => {
			setTimeout(() => {
				resolve(true);
			}, time);
		});
	};
	const editTrigger = (
		<span>
			{intl.formatMessage({
				id: "buttons.edit",
			})}
		</span>
	);
	const createTrigger = (
		<Button type="primary">
			<PlusOutlined />
			{intl.formatMessage({
				id: "buttons.create",
			})}
		</Button>
	);
	return (
		<>
			<ModalForm<IFormData>
				title={intl.formatMessage({
					id: prop.edit ? "buttons.edit" : "buttons.create",
				})}
				trigger={prop.edit ? editTrigger : createTrigger}
				form={form}
				autoFocusFirstInput
				modalProps={{
					destroyOnClose: true,
					onCancel: () => console.log("run"),
				}}
				submitTimeout={2000}
				onFinish={async (values) => {
					await waitTime(2000);
					console.log(values.word);
					message.success("提交成功");
					return true;
				}}
				request={async () => {
					if (
						typeof prop.edit !== "undefined" &&
						prop.edit === true
					) {
						// 如果是编辑，就从服务器拉取数据。
						let url = "/v2/terms/" + (prop.edit ? prop.wordId : "");
						console.log(url);
						const res = await get<ITermResponse>(url);
						console.log(res);
						setLang(res.data.language);
						return {
							word: res.data.word,
							tag: res.data.tag,
							meaning: res.data.meaning,
							meaning2: res.data.other_meaning,
							note: res.data.note,
							lang: res.data.language,
							channel: res.data.channal,
						};
					} else if (typeof prop.word !== "undefined") {
						setLang("zh-Hans");
						return {
							word: prop.word ? prop.word : "",
							tag: "",
							meaning: "",
							meaning2: "",
							note: "",
							lang: "zh-Hans",
							channel: "",
						};
					} else {
						setLang("zh-Hans");
						return {
							word: "",
							tag: "",
							meaning: "",
							meaning2: "",
							note: "",
							lang: "zh-Hans",
							channel: "",
						};
					}
				}}
			>
				<ProForm.Group>
					<ProFormText
						width="md"
						name="word"
						required
						label={intl.formatMessage({
							id: "dict.fields.word.label",
						})}
						rules={[
							{
								required: true,
								message: intl.formatMessage({
									id: "channel.create.message.noname",
								}),
							},
						]}
					/>
					<ProFormText
						width="md"
						name="tag"
						tooltip={intl.formatMessage({
							id: "term.fields.description.tooltip",
						})}
						label={intl.formatMessage({
							id: "term.fields.description.label",
						})}
					/>
				</ProForm.Group>
				<ProForm.Group>
					<ProFormText
						width="md"
						name="meaning"
						tooltip={intl.formatMessage({
							id: "term.fields.meaning.tooltip",
						})}
						label={intl.formatMessage({
							id: "term.fields.meaning.label",
						})}
					/>
					<ProFormText
						width="md"
						name="meaning2"
						tooltip={intl.formatMessage({
							id: "term.fields.meaning2.tooltip",
						})}
						label={intl.formatMessage({
							id: "term.fields.meaning2.label",
						})}
					/>
				</ProForm.Group>
				<ProForm.Group>
					<ProFormText
						width="md"
						name="channel"
						tooltip={intl.formatMessage({
							id: "term.fields.channel.tooltip",
						})}
						label={intl.formatMessage({
							id: "term.fields.channel.label",
						})}
					/>

					<Form.Item
						name="lang"
						label={intl.formatMessage({
							id: "forms.fields.lang.label",
						})}
					>
						<SelectLang lang={lang} />
					</Form.Item>
				</ProForm.Group>
				<ProForm.Group>
					<ProFormTextArea
						name="note"
						width="xl"
						label={intl.formatMessage({
							id: "forms.fields.note.label",
						})}
					/>
				</ProForm.Group>
			</ModalForm>
		</>
	);
};

export default Widget;
