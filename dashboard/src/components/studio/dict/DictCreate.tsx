import { ProForm, ProFormText } from "@ant-design/pro-components";
import { Cascader, Layout, Select } from "antd";
import { useIntl } from "react-intl";
import { message } from "antd";

const { Option, OptGroup } = Select;

interface CascaderOption {
	value: string | number;
	label: string;
	children?: CascaderOption[];
}

interface IFormData {
	word: string;
	type: string;
	grammar: string;
	parent: string;
	meaning: string;
	note: string;
	factors: string;
	factormeaning: string;
	lang: string;
}

type IWidgetDictCreate = {
	studio: string | undefined;
};
const Widget = (param: IWidgetDictCreate) => {
	const intl = useIntl();

	const data = [
		{ value: "en", lable: intl.formatMessage({ id: "languages.en-US" }) },
		{ value: "zh-Hans", lable: intl.formatMessage({ id: "languages.zh-Hans" }) },
		{ value: "zh-Hant", lable: intl.formatMessage({ id: "languages.zh-Hant" }) },
	];
	const langOptions = data.map((d) => <Option value={d.value}>{d.lable}</Option>);

	const case8 = [
		{
			value: "nom",
			label: intl.formatMessage({ id: "dict.fields.type.nom.label" }),
		},
		{
			value: "acc",
			label: intl.formatMessage({ id: "dict.fields.type.acc.label" }),
		},
	];
	const case2 = [
		{
			value: "sg",
			label: intl.formatMessage({ id: "dict.fields.type.sg.label" }),
			children: case8,
		},
		{
			value: "pl",
			label: intl.formatMessage({ id: "dict.fields.type.pl.label" }),
			children: case8,
		},
	];
	const case3 = [
		{
			value: "m",
			label: intl.formatMessage({ id: "dict.fields.type.m.label" }),
			children: case2,
		},
		{
			value: "nt",
			label: intl.formatMessage({ id: "dict.fields.type.nt.label" }),
			children: case2,
		},
		{
			value: "f",
			label: intl.formatMessage({ id: "dict.fields.type.f.label" }),
			children: case2,
		},
	];
	const options: CascaderOption[] = [
		{
			value: ".n.",
			label: intl.formatMessage({ id: "dict.fields.type.n.label" }),
			children: case3,
		},
		{
			value: ".ti.",
			label: intl.formatMessage({ id: "dict.fields.type.ti.label" }),
			children: case3,
		},
		{
			value: ".v.",
			label: intl.formatMessage({ id: "dict.fields.type.v.label" }),
			children: case3,
		},
	];

	const onLangChange = (value: string) => {
		console.log(`selected ${value}`);
	};

	const onLangSearch = (value: string) => {
		console.log("search:", value);
	};
	return (
		<Layout>
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
						name="word"
						required
						label={intl.formatMessage({ id: "dict.fields.word.label" })}
						rules={[
							{
								required: true,
								message: intl.formatMessage({ id: "channel.create.message.noname" }),
							},
						]}
					/>
				</ProForm.Group>
				<ProForm.Group>
					<Cascader options={options} placeholder="Please select case" />
				</ProForm.Group>

				<ProForm.Group>
					<Select
						showSearch
						placeholder="Select a language"
						optionFilterProp="children"
						onChange={onLangChange}
						onSearch={onLangSearch}
						filterOption={(input, option) =>
							(option!.children as unknown as string).toLowerCase().includes(input.toLowerCase())
						}
					>
						<OptGroup label="recent">
							<Option value="zh-Hans">简体中文</Option>
							<Option value="en">English</Option>
						</OptGroup>
						<OptGroup label="all">{langOptions}</OptGroup>
					</Select>
				</ProForm.Group>
			</ProForm>
		</Layout>
	);
};

export default Widget;
