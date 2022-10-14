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
	const listdata: ListNodeData[] = [
		{ article: "1", title: "title1", level: 1 },
		{ article: "2", title: "title2", level: 2 },
		{ article: "3", title: "title3", level: 1 },
		{ article: "4", title: "title4", level: 2 },
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
