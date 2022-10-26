import { useParams } from "react-router-dom";
import { useIntl } from "react-intl";

import { Layout } from "antd";

import { ProForm, ProFormText, ProFormSelect, ProFormTextArea } from "@ant-design/pro-components";
import { message } from "antd";

import HeadBar from "../../../components/studio/HeadBar";
import LeftSider from "../../../components/studio/LeftSider";
import Footer from "../../../components/studio/Footer";
import EditableTree from "../../../components/studio/EditableTree";
import type { ListNodeData } from "../../../components/studio/EditableTree";

const { Content } = Layout;

interface IFormData {
	title: string;
	subtitle: string;
	summary: string;
	lang: string;
	studio: string;
	toc: string;
}

const Widget = () => {
	const intl = useIntl();
	const { studioname, anthology_id } = useParams(); //url 参数
	/*
	const listdata: ListNodeData[] = [
		{ key: "1", title: "title1", level: 1 },
		{ key: "2", title: "title2", level: 2 },
		{ key: "3", title: "title3", level: 1 },
		{ key: "4", title: "title4", level: 2 },
	];
	*/
	const listdata: ListNodeData[] = [
		{
			key: "d391c9c4-60bc-4bf5-8f5a-65d55743904c",
			title: "比库尼八敬法--Aṭṭhagarudhammā",
			level: 1,
		},
		{
			key: "4b741bea-811e-4053-a94c-852a58161b8f",
			title: "逐出比库尼僧团之《第一极重罪》",
			level: 1,
		},
		{
			key: "33ff0ec7-2cf9-4e3a-b88f-e713c7a1eaa5",
			title: "Aṭṭhagarudhammā",
			level: 1,
		},
	];
	return (
		<Layout>
			<HeadBar />
			<Layout>
				<LeftSider selectedKeys="anthology" />
				<Content>
					<h2>
						studio/{studioname}/{intl.formatMessage({ id: "columns.studio.anthology.title" })}/anthology/
						{anthology_id}
					</h2>

					<ProForm<IFormData>
						onFinish={async (values: IFormData) => {
							// TODO
							values.studio = "aaaa";
							console.log(values);
							message.success(intl.formatMessage({ id: "flashes.success" }));
						}}
					>
						<ProForm.Group>
							<ProFormText
								width="md"
								name="title"
								required
								label={intl.formatMessage({ id: "forms.fields.title.label" })}
								rules={[
									{
										required: true,
										message: intl.formatMessage({ id: "forms.create.message.no.title" }),
									},
								]}
							/>
						</ProForm.Group>
						<ProForm.Group>
							<ProFormText
								width="md"
								name="subtitle"
								label={intl.formatMessage({ id: "forms.fields.subtitle.label" })}
							/>
						</ProForm.Group>
						<ProForm.Group>
							<ProFormTextArea
								name="summary"
								label={intl.formatMessage({ id: "forms.fields.summary.label" })}
							/>
						</ProForm.Group>
						<ProForm.Group>
							<ProFormSelect
								options={[
									{ value: "zh-Hans", label: "简体中文" },
									{ value: "zh-Hant", label: "繁体中文" },
									{ value: "en-US", label: "English" },
								]}
								width="md"
								name="lang"
								rules={[
									{
										required: true,
										message: intl.formatMessage({ id: "forms.create.message.no.lang" }),
									},
								]}
								label={intl.formatMessage({ id: "channel.lang" })}
							/>
						</ProForm.Group>

						<ProForm.Group>
							<ProFormTextArea
								name="toc"
								label={intl.formatMessage({ id: "forms.fields.content.label" })}
							/>
						</ProForm.Group>
					</ProForm>

					<EditableTree treeData={listdata} />
				</Content>
			</Layout>
			<Footer />
		</Layout>
	);
};

export default Widget;
