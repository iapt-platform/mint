import { useParams } from "react-router-dom";
import { ProForm, ProFormText , ProFormSelect,ProFormTextArea } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { Link } from "react-router-dom";
import { Space,message } from "antd";
interface IFormData {
	name: string;
	type: string;
	lang: string;
	summary: string;
	studio: string;
  }
const Widget = () => {
	const intl = useIntl();
	const { studioid } = useParams();
  return (
    <div>
      <div>studio/{studioid}/{intl.formatMessage({ id: "title.channel" })}/create</div>
      <div>
        <div>
          <Space>
            <Link to="/">Home</Link>
            <Link to="/community/myread">{studioid}</Link>
          </Space>
        </div>
		
		<div>
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
			name="name"
			required
			label={intl.formatMessage({ id: "channel.name" })}
			rules={[{ required: true, message: intl.formatMessage({ id: "channel.create.message.noname" }) }]}
			/>
		</ProForm.Group>
		
		<ProForm.Group>
			<ProFormSelect
				options={[
					{value: 'translation', label: intl.formatMessage({ id: "channel.type.translation.title" }), },
					{value: 'nissaya', label: intl.formatMessage({ id: "channel.type.nissaya.title" }), },
				]}
				width="md"
				name="type"
				rules={[{ required: true, message: intl.formatMessage({ id: "channel.create.message.noname" }) }]}
				label={intl.formatMessage({ id: "channel.type" })}
				/>
		</ProForm.Group>
		<ProForm.Group>
			<ProFormSelect
				options={[
					{value: 'zh-Hans', label: "简体中文", },
					{value: 'zh-Hant', label: "繁体中文", },
					{value: 'en-US', label: "English", },
				]}
				width="md"
				name="lang"
				rules={[{ required: true, message: intl.formatMessage({ id: "channel.create.message.noname" }) }]}
				label={intl.formatMessage({ id: "channel.lang" })}
				/>
		</ProForm.Group>

		<ProForm.Group>
			<ProFormTextArea  name="summary" label="简介" />
		</ProForm.Group>

		</ProForm>
		</div>
      </div>
      <div>底部区域</div>
    </div>
  );
};

export default Widget;
