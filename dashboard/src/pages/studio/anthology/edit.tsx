import { useParams } from "react-router-dom";
import { useIntl } from "react-intl";

import {
	ProForm,
	ProFormText,
	ProFormTextArea,
} from "@ant-design/pro-components";
import { Card, Col, message, Row } from "antd";

import EditableTree from "../../../components/studio/EditableTree";
import type { ListNodeData } from "../../../components/studio/EditableTree";
import LangSelect from "../../../components/studio/LangSelect";
import PublicitySelect from "../../../components/studio/PublicitySelect";
import {
	IAnthologyDataRequest,
	IAnthologyResponse,
} from "../../../components/api/Article";
import { get, put } from "../../../request";
import { useState } from "react";

interface IFormData {
	title: string;
	subtitle: string;
	summary: string;
	lang: string;
	status: number;
}

const Widget = () => {
	const listdata: ListNodeData[] = [];
	const intl = useIntl();
	const [tocData, setTocData] = useState(listdata);
	const [title, setTitle] = useState("");
	const { anthology_id } = useParams(); //url 参数

	return (
		<>
			<Card title={title} style={{ margin: "1em" }}>
				<Row>
					<Col>
						<ProForm<IFormData>
							onFinish={async (values: IFormData) => {
								// TODO
								console.log(values);
								/*
					const request: IAnthologyDataRequest = {
						title: values.title,
						subtitle: values.subtitle,
						summary: values.summary,
						article_list:[];
						status: values.status,
						lang: values.lang,
					};
					console.log(request);
					const res = await put<
						IAnthologyDataRequest,
						IAnthologyResponse
					>(`/v2/article/${anthology_id}`, request);
					console.log(res);
					if (res.ok) {
						message.success(
							intl.formatMessage({ id: "flashes.success" })
						);
					} else {
						message.error(res.message);
					}
					*/
							}}
							request={async () => {
								const res = await get<IAnthologyResponse>(
									`/v2/anthology/${anthology_id}`
								);
								const toc: ListNodeData[] =
									res.data.article_list.map((item) => {
										return {
											key: item.article,
											title: item.title,
											level: parseInt(item.level),
										};
									});
								setTocData(toc);
								setTitle(res.data.title);
								return {
									title: res.data.title,
									subtitle: res.data.subtitle,
									summary: res.data.summary,
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
						</ProForm>
					</Col>
					<Col>
						<EditableTree treeData={tocData} />
					</Col>
				</Row>
			</Card>
		</>
	);
};

export default Widget;
