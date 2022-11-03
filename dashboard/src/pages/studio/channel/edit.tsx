import { useParams } from "react-router-dom";
import {
	ProForm,
	ProFormText,
	ProFormSelect,
	ProFormTextArea,
} from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { message, Space } from "antd";
import { IApiResponseChannel } from "../../../components/api/Channel";
import { get, put } from "../../../request";
import ChannelTypeSelect from "../../../components/studio/channel/ChannelTypeSelect";
import LangSelect from "../../../components/studio/LangSelect";
import PublicitySelect from "../../../components/studio/PublicitySelect";

interface IFormData {
	name: string;
	type: string;
	lang: string;
	summary: string;
	status: number;
	studio: string;
}
const Widget = () => {
	const intl = useIntl();
	const { channelid } = useParams(); //url 参数
	const { studioname } = useParams();

	return (
		<>
			<Space>{channelid}</Space>
			<ProForm<IFormData>
				onFinish={async (values: IFormData) => {
					// TODO
					console.log(values);
					const res = await put(`/v2/channel/${channelid}`, values);
					console.log(res);
					message.success(
						intl.formatMessage({ id: "flashes.success" })
					);
				}}
				formKey="channel_edit"
				request={async () => {
					// TODO
					const res: IApiResponseChannel = await get(
						`/v2/channel/${channelid}`
					);
					return {
						name: res.data.name,
						type: res.data.type,
						lang: res.data.lang,
						summary: res.data.summary,
						status: res.data.status,
						studio: studioname ? studioname : "",
					};
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
								message: intl.formatMessage({
									id: "channel.create.message.noname",
								}),
							},
						]}
					/>
				</ProForm.Group>

				<ProForm.Group>
					<ChannelTypeSelect />
					<LangSelect />
				</ProForm.Group>
				<ProForm.Group>
					<PublicitySelect />
				</ProForm.Group>

				<ProForm.Group>
					<ProFormTextArea width="md" name="summary" label="简介" />
				</ProForm.Group>
			</ProForm>
		</>
	);
};

export default Widget;
