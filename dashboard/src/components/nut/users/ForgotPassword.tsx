import { useIntl } from "react-intl";
import { ProForm, ProFormText } from "@ant-design/pro-components";
import { message } from "antd";

import { post } from "../../../request";
import { useState } from "react";

interface IFormData {
	email: string;
}
interface IForgotPasswordResponse {
	ok: boolean;
	message: string;
	data: string;
}
const Widget = () => {
	const intl = useIntl();
	const [notify, setNotify] = useState(
		"系统将向您的注册邮箱发送包含重置密码所需信息的链接。请输入您的注册邮箱。并确保该邮箱可以接受邮件。"
	);

	return (
		<>
			<div>{notify}</div>
			<ProForm<IFormData>
				onFinish={async (values: IFormData) => {
					// TODO
					console.log(values);
					const user = {
						email: values.email,
					};
					const signin = await post<
						IFormData,
						IForgotPasswordResponse
					>("/v2/auth/forgotpassword", user);
					if (signin.ok) {
						console.log("token", signin.data);
						setNotify("重置密码的邮件已经发送到您的邮箱。");
						message.success(
							intl.formatMessage({ id: "flashes.success" })
						);
					} else {
						message.error(signin.message);
					}
				}}
			>
				<ProForm.Group>
					<ProFormText
						width="md"
						name="email"
						required
						label={intl.formatMessage({
							id: "forms.fields.email.label",
						})}
						rules={[
							{ required: true, type: "email", max: 255, min: 6 },
						]}
					/>
				</ProForm.Group>
			</ProForm>
		</>
	);
};

export default Widget;
