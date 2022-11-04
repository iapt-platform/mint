import { useParams } from "react-router-dom";
import { useIntl } from "react-intl";
import {
	ProForm,
	ProFormText,
	ProFormSelect,
	ProFormTextArea,
} from "@ant-design/pro-components";
import { message } from "antd";
import { get, put } from "../../../request";
import {
	IArticleDataRequest,
	IArticleResponse,
} from "../../../components/api/Article";
import LangSelect from "../../../components/studio/LangSelect";
import PublicitySelect from "../../../components/studio/PublicitySelect";

interface IFormData {
	uid: string;
	title: string;
	subtitle: string;
	summary: string;
	content: string;
	content_type: string;
	status: number;
	lang: string;
}

const Widget = () => {
	const intl = useIntl();
	const { studioname, articleid } = useParams(); //url 参数
	return (
		<>
			<h2>
				studio/{studioname}/
				{intl.formatMessage({ id: "columns.studio.article.title" })}
				/edit/
				{articleid}
			</h2>

			<ProForm<IFormData>
				onFinish={async (values: IFormData) => {
					// TODO

					const request = {
						uid: articleid ? articleid : "",
						title: values.title,
						subtitle: values.subtitle,
						summary: values.summary,
						content: values.content,
						content_type: "markdown",
						status: values.status,
						lang: values.lang,
					};
					console.log(request);
					const res = await put<
						IArticleDataRequest,
						IArticleResponse
					>(`/v2/article/${articleid}`, request);
					console.log(res);
					if (res.ok) {
						message.success(
							intl.formatMessage({ id: "flashes.success" })
						);
					} else {
						message.error(res.message);
					}
				}}
				request={async () => {
					const res = await get<IArticleResponse>(
						`/v2/article/${articleid}`
					);
					return {
						uid: res.data.uid,
						title: res.data.title,
						subtitle: res.data.subtitle,
						summary: res.data.summary,
						content: res.data.content,
						content_type: res.data.content_type,
						lang: res.data.lang,
						status: res.data.status,
					};
				}}
			>
				<ProForm.Group>
					<ProFormText
						width="md"
						name="title"
						required
						label={intl.formatMessage({
							id: "forms.fields.title.label",
						})}
						rules={[
							{
								required: true,
								message: intl.formatMessage({
									id: "forms.message.title.required",
								}),
							},
						]}
					/>
				</ProForm.Group>
				<ProForm.Group>
					<ProFormText
						width="md"
						name="subtitle"
						label={intl.formatMessage({
							id: "forms.fields.subtitle.label",
						})}
					/>
				</ProForm.Group>
				<ProForm.Group>
					<ProFormTextArea
						name="summary"
						width="md"
						label={intl.formatMessage({
							id: "forms.fields.summary.label",
						})}
					/>
				</ProForm.Group>
				<ProForm.Group>
					<LangSelect />
				</ProForm.Group>
				<ProForm.Group>
					<PublicitySelect />
				</ProForm.Group>
				<ProForm.Group>
					<ProFormTextArea
						name="content"
						width="md"
						label={intl.formatMessage({
							id: "forms.fields.content.label",
						})}
					/>
				</ProForm.Group>
			</ProForm>
		</>
	);
};

export default Widget;
