import {
	ProForm,
	ProFormText,
	ProFormTextArea,
} from "@ant-design/pro-components";
import { Layout } from "antd";
import { useIntl } from "react-intl";
import { message } from "antd";

import SelectLang from "../SelectLang";
import SelectCase from "../SelectCase";
import Confidene from "../Confidence";
import DictEditInner from "./DictEditInner";

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
	studio: string;
	word?: string;
};
const Widget = (prop: IWidgetDictCreate) => {
	const intl = useIntl();
	/*
	const onLangChange = (value: string) => {
		console.log(`selected ${value}`);
	};

	const onLangSearch = (value: string) => {
		console.log("search:", value);
	};
	*/
	return (
		<>
			<ProForm<IFormData>
				onFinish={async (values: IFormData) => {
					// TODO
					console.log(values);
					message.success(
						intl.formatMessage({ id: "flashes.success" })
					);
				}}
			>
				<DictEditInner word={prop.word} />
			</ProForm>
		</>
	);
};

export default Widget;
