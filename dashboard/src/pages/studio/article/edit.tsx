import { useParams } from "react-router-dom";
import { useIntl } from "react-intl";
import { Layout } from "antd";
import { ProForm, ProFormText, ProFormSelect, ProFormTextArea } from "@ant-design/pro-components";
import { message } from "antd";

import HeadBar from "../../../components/studio/HeadBar";
import LeftSider from "../../../components/studio/LeftSider";
import Footer from "../../../components/studio/Footer";
const { Content } = Layout;

interface IFormData {
	title: string;
	subtitle: string;
	summary: string;
	lang: string;
	studio: string;
	content: string;
}

const Widget = () => {
	const intl = useIntl();
	const { studioname, articleid } = useParams(); //url 参数
	return (
		<Layout>
			<HeadBar />
			<Layout>
				<LeftSider selectedKeys="article" />
				<Content>
					<h2>
						studio/{studioname}/{intl.formatMessage({ id: "columns.studio.article.title" })}/edit/
						{articleid}
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
								name="content"
								label={intl.formatMessage({ id: "forms.fields.content.label" })}
							/>
						</ProForm.Group>
					</ProForm>
				</Content>
			</Layout>
			<Footer />
		</Layout>
	);
};

export default Widget;
