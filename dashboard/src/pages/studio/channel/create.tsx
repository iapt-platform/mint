import { useParams } from "react-router-dom";
import { ProForm, ProFormText, ProFormSelect } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { Link } from "react-router-dom";
import { Space, message } from "antd";
interface IFormData {
	name: string;
	type: string;
}
const Widget = () => {
	const intl = useIntl();
	const { studioname } = useParams();

	return (
		<div>
			<div>
				studio/{studioname}/{intl.formatMessage({ id: "title.channel" })}/create
			</div>
			<div>
				<div>
					<Space>
						<Link to="/">Home</Link>
						<Link to="/community/myread">{studioname}</Link>
					</Space>
				</div>

				<div>
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
								name="name"
								required
								label={intl.formatMessage({ id: "channel.name" })}
								rules={[
									{
										required: true,
										message: intl.formatMessage({ id: "channel.create.message.noname" }),
									},
								]}
							/>
						</ProForm.Group>
						<ProForm.Group>
							<ProFormSelect
								options={[
									{
										value: "translation",
										label: intl.formatMessage({ id: "channel.type.translation.title" }),
									},
									{
										value: "nissaya",
										label: intl.formatMessage({ id: "channel.type.nissaya.title" }),
									},
								]}
								width="md"
								name="type"
								label={intl.formatMessage({ id: "channel.type" })}
							/>
						</ProForm.Group>
					</ProForm>
				</div>
			</div>
			<div>底部区域</div>
		</div>
	);
};

export default Widget;
