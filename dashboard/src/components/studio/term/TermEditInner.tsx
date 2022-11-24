import { useIntl } from "react-intl";

import {
  ProForm,
  ProFormText,
  ProFormTextArea,
} from "@ant-design/pro-components";

import LangSelect from "../LangSelect";

const Widget = () => {
  const intl = useIntl();
  return (
    <>
      <ProForm.Group>
        <ProFormText
          width="md"
          name="word"
          required
          label={intl.formatMessage({
            id: "dict.fields.word.label",
          })}
          rules={[
            {
              required: true,
              message: intl.formatMessage({
                id: "channel.create.message.noname",
              }),
            },
          ]}
        />
        <ProFormText
          width="md"
          name="tag"
          tooltip={intl.formatMessage({
            id: "term.fields.description.tooltip",
          })}
          label={intl.formatMessage({
            id: "term.fields.description.label",
          })}
        />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormText
          width="md"
          name="meaning"
          tooltip={intl.formatMessage({
            id: "term.fields.meaning.tooltip",
          })}
          label={intl.formatMessage({
            id: "term.fields.meaning.label",
          })}
        />
        <ProFormText
          width="md"
          name="meaning2"
          tooltip={intl.formatMessage({
            id: "term.fields.meaning2.tooltip",
          })}
          label={intl.formatMessage({
            id: "term.fields.meaning2.label",
          })}
        />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormText
          width="md"
          name="channel"
          tooltip={intl.formatMessage({
            id: "term.fields.channel.tooltip",
          })}
          label={intl.formatMessage({
            id: "term.fields.channel.label",
          })}
        />

        <LangSelect />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormTextArea
          name="note"
          width="xl"
          label={intl.formatMessage({
            id: "forms.fields.note.label",
          })}
        />
      </ProForm.Group>
    </>
  );
};

export default Widget;
