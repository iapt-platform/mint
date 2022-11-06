import {
	ProForm,
	ProFormText,
	ProFormSelect,
} from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { message } from "antd";
import LangSelect from "../LangSelect";
import { IAnthologyCreateRequest, IAnthologyResponse } from "../../api/Article";
import { post } from "../../../request";

interface IFormData {
	title: string;
	lang: string;
	studio: string;
}

type IWidgetAnthologyCreate = {
	studio?: string;
};
const Widget = (prop: IWidgetAnthologyCreate) => {
	const intl = useIntl();

	return (
		<ProForm<IFormData>
			onFinish={async (values: IFormData) => {
				// TODO
				values.studio = prop.studio ? prop.studio : "";
				console.log(values);
				const res = await post<
					IAnthologyCreateRequest,
					IAnthologyResponse
				>(`/v2/anthology`, values);
				console.log(res);
				if (res.ok) {
					message.success(
						intl.formatMessage({ id: "flashes.success" })
					);
				} else {
					message.error(res.message);
				}
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
							max: 255,
						},
					]}
				/>
			</ProForm.Group>
			<ProForm.Group>
				<LangSelect />
			</ProForm.Group>
		</ProForm>
	);
};

export default Widget;
