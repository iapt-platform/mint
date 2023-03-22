import { useIntl } from "react-intl";

import {
  ProForm,
  ProFormSelect,
  ProFormText,
  ProFormTextArea,
} from "@ant-design/pro-components";

import LangSelect from "../general/LangSelect";
import { DefaultOptionType } from "antd/lib/select";

interface IWidget {
  meaningList?: string[];
  channelList?: DefaultOptionType[];
}
const Widget = ({ meaningList, channelList }: IWidget) => {
  const intl = useIntl();
  return (
    <>
      <ProForm.Group>
        <ProFormText width="md" name="id" hidden />
        <ProFormText
          width="md"
          name="word"
          required
          label={intl.formatMessage({
            id: "term.fields.word.label",
          })}
          rules={[
            {
              required: true,
              message: intl.formatMessage({
                id: "term.message.meaning.required",
              }),
            },
          ]}
          fieldProps={{
            showCount: true,
            maxLength: 128,
          }}
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
            id: "forms.fields.meaning.label",
          })}
          rules={[
            {
              required: true,
              message: intl.formatMessage({
                id: "forms.message.meaning.required",
              }),
            },
          ]}
          fieldProps={{
            showCount: true,
            maxLength: 128,
          }}
        />
        <ProFormSelect
          width="md"
          name="meaning2"
          label={intl.formatMessage({
            id: "term.fields.meaning2.label",
          })}
          options={meaningList}
          fieldProps={{
            mode: "tags",
            tokenSeparators: [",", "，"],
          }}
          placeholder="Please select other meanings"
          rules={[
            {
              type: "array",
            },
          ]}
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
