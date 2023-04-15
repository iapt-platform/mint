import { useIntl } from "react-intl";

import {
  ProForm,
  ProFormInstance,
  ProFormSelect,
  ProFormText,
  ProFormTextArea,
} from "@ant-design/pro-components";

import LangSelect from "../general/LangSelect";
import ChannelSelect from "../channel/ChannelSelect";
import { Form, message } from "antd";
import { useRef } from "react";
import {
  ITermCreateResponse,
  ITermDataRequest,
  ITermResponse,
} from "../api/Term";
import { get, post, put } from "../../request";
import MDEditor from "@uiw/react-md-editor";

export interface ITerm {
  id?: string;
  word?: string;
  tag?: string;
  meaning?: string;
  meaning2?: string[];
  note?: string;
  channel?: string[];
  channelId?: string;
  lang?: string;
}

interface IWidget {
  id?: string;
  word?: string;
  studioName?: string;
  channelId?: string;
  onUpdate?: Function;
}
const Widget = ({ id, word, channelId, studioName, onUpdate }: IWidget) => {
  const intl = useIntl();
  const [form] = Form.useForm<ITerm>();
  const formRef = useRef<ProFormInstance>();
  return (
    <ProForm<ITerm>
      form={form}
      formRef={formRef}
      autoFocusFirstInput={true}
      onFinish={async (values: ITerm) => {
        console.log(values.word);
        if (
          typeof values.word === "undefined" ||
          typeof values.meaning === "undefined"
        ) {
          return;
        }
        const newValue = {
          id: values.id,
          word: values.word,
          tag: values.tag,
          meaning: values.meaning,
          other_meaning: values.meaning2?.join(),
          note: values.note,
          channal: values.channel
            ? values.channel[values.channel.length - 1]
            : undefined,
          studioName: studioName,
          language: values.lang,
        };
        let res: ITermResponse;
        if (typeof values.id === "undefined") {
          res = await post<ITermDataRequest, ITermResponse>(
            `/v2/terms`,
            newValue
          );
        } else {
          res = await put<ITermDataRequest, ITermResponse>(
            `/v2/terms/${values.id}`,
            newValue
          );
        }
        console.log(res);
        if (res.ok) {
          message.success("提交成功");
          if (typeof onUpdate !== "undefined") {
            onUpdate();
          }
        } else {
          message.error(res.message);
        }

        return true;
      }}
      request={async () => {
        let url: string;
        let data: ITerm = {
          word: "",
          tag: "",
          meaning: "",
          meaning2: [],
          note: "",
          lang: "",
          channel: [],
        };
        if (typeof id !== "undefined") {
          // 如果是编辑，就从服务器拉取数据。
          url = "/v2/terms/" + id;
          console.log("url", url);
          const res = await get<ITermResponse>(url);
          console.log("request", res);
          let meaning2: string[] = [];
          if (res.data.other_meaning) {
            meaning2 = res.data.other_meaning.split(",");
          }

          return {
            id: res.data.guid,
            word: res.data.word,
            tag: res.data.tag,
            meaning: res.data.meaning,
            meaning2: meaning2,
            note: res.data.note,
            lang: res.data.language,
            channel: res.data.channel
              ? [res.data.studio.id, res.data.channel?.id]
              : undefined,
          };
        } else if (typeof channelId !== "undefined") {
          //在channel新建
          url = `/v2/terms?view=create-by-channel&channel=${channelId}&word=${word}`;
          const res = await get<ITermCreateResponse>(url);
          console.log(res);
          data = {
            word: word ? word : "",
            tag: "",
            meaning: "",
            meaning2: [],
            note: "",
            lang: res.data.language,
            channel: [res.data.studio.id, channelId],
          };
          return data;
        } else if (typeof studioName !== "undefined") {
          //在studio新建
          url = `/v2/terms?view=create-by-studio&studio=${studioName}&word=${word}`;
        } else {
          return {
            word: "",
            tag: "",
            meaning: "",
            meaning2: [],
            note: "",
            lang: "",
            channel: [],
          };
        }
        return data;
      }}
    >
      <ProForm.Group>
        <ProFormText width="md" name="id" hidden />
        <ProFormText
          width="md"
          name="word"
          initialValue={word}
          required
          label={intl.formatMessage({
            id: "term.fields.word.label",
          })}
          rules={[
            {
              required: true,
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
        <ChannelSelect
          channelId={channelId}
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
        <Form.Item
          style={{ width: "100%" }}
          name="note"
          label={intl.formatMessage({ id: "forms.fields.note.label" })}
        >
          <MDEditor />
        </Form.Item>
      </ProForm.Group>
    </ProForm>
  );
};

export default Widget;
