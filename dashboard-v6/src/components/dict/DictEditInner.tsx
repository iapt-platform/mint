import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormText,
  ProFormTextArea,
} from "@ant-design/pro-components";

import LangSelect from "../general/LangSelect";
import SelectCase from "./SelectCase";
import Confidence from "./Confidence";

type IWidgetDictCreate = {
  word?: string;
};
const DictEditInnerWidget = (prop: IWidgetDictCreate) => {
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
      <ProForm.Group>
        <ProFormText
          width="md"
          name="word"
          initialValue={prop.word}
          required
          label={intl.formatMessage({ id: "dict.fields.word.label" })}
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
        <div>语法信息</div>
        <SelectCase />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormText
          width="md"
          name="type"
          label={intl.formatMessage({
            id: "dict.fields.type.label",
          })}
        />
        <ProFormText
          width="md"
          name="grammar"
          label={intl.formatMessage({
            id: "dict.fields.grammar.label",
          })}
        />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormText
          width="md"
          name="parent"
          label={intl.formatMessage({
            id: "dict.fields.parent.label",
          })}
        />
      </ProForm.Group>
      <ProForm.Group>
        <LangSelect />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormText
          width="md"
          name="meaning"
          label={intl.formatMessage({
            id: "dict.fields.meaning.label",
          })}
        />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormText
          width="md"
          name="factors"
          label={intl.formatMessage({
            id: "dict.fields.factors.label",
          })}
        />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormText
          width="md"
          name="factormeaning"
          label={intl.formatMessage({
            id: "dict.fields.factormeaning.label",
          })}
        />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormTextArea
          name="note"
          label={intl.formatMessage({
            id: "forms.fields.note.label",
          })}
        />
      </ProForm.Group>
      <ProForm.Group>
        <Confidence />
      </ProForm.Group>
    </>
  );
};

export default DictEditInnerWidget;
