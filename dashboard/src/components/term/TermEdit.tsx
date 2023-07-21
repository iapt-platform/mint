import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormInstance,
  ProFormSelect,
  ProFormText,
} from "@ant-design/pro-components";

import LangSelect from "../general/LangSelect";
import ChannelSelect from "../channel/ChannelSelect";
import { AutoComplete, Form, Input, message, Space, Tag } from "antd";
import { useEffect, useRef, useState } from "react";
import {
  ITermCreateResponse,
  ITermDataRequest,
  ITermListResponse,
  ITermResponse,
} from "../api/Term";
import { get, post, put } from "../../request";
import MDEditor from "@uiw/react-md-editor";

interface ValueType {
  key?: string;
  label: React.ReactNode;
  value: string | number;
}

export interface ITerm {
  id?: string;
  word?: string;
  tag?: string;
  meaning?: string;
  meaning2?: string[];
  note?: string;
  summary?: string;
  channel?: string[];
  channelId?: string;
  lang?: string;
}

interface IWidget {
  id?: string;
  word?: string;
  studioName?: string;
  channelId?: string;
  parentChannelId?: string;
  parentStudioId?: string;
  onUpdate?: Function;
}
const TermEditWidget = ({
  id,
  word,
  channelId,
  studioName,
  parentChannelId,
  parentStudioId,
  onUpdate,
}: IWidget) => {
  const intl = useIntl();
  const [meaningOptions, setMeaningOptions] = useState<ValueType[]>([
    { label: "dd", value: "dd" },
  ]);

  const [form] = Form.useForm<ITerm>();
  const formRef = useRef<ProFormInstance>();
  useEffect(() => {
    if (word) {
      const url = `/v2/terms?view=word&word=${word}`;
      get<ITermListResponse>(url).then((json) => {
        const meaning = json.data.rows.map((item) => item.meaning);
        let meaningMap = new Map<string, number>();
        for (const it of meaning) {
          const count = meaningMap.get(it);
          if (typeof count === "undefined") {
            meaningMap.set(it, 1);
          } else {
            meaningMap.set(it, count + 1);
          }
        }
        const meaningList: ValueType[] = [];
        meaningMap.forEach((value, key, map) => {
          meaningList.push({
            value: key,
            label: (
              <Space
                style={{ display: "flex", justifyContent: "space-between" }}
              >
                {key}
                <Tag>{value}</Tag>
              </Space>
            ),
          });
        });

        setMeaningOptions(meaningList);
      });
    }
  }, [word]);
  return (
    <ProForm<ITerm>
      form={form}
      formRef={formRef}
      autoFocusFirstInput={true}
      onFinish={async (values: ITerm) => {
        console.log("term submit", values);
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
          studioId: parentStudioId,
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

        if (res.ok) {
          message.success("提交成功");
          if (typeof onUpdate !== "undefined") {
            onUpdate(res.data);
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

          data = {
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
            channel: [""],
          };
        } else if (typeof studioName !== "undefined") {
          //在studio新建
          url = `/v2/terms?view=create-by-studio&studio=${studioName}&word=${word}`;
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
        <Form.Item
          name="meaning"
          label={intl.formatMessage({
            id: "term.fields.meaning.label",
          })}
          rules={[
            {
              required: true,
            },
          ]}
        >
          <AutoComplete
            options={meaningOptions}
            onChange={(value: any) => {}}
            maxLength={128}
          >
            <Input allowClear showCount={true} />
          </AutoComplete>
        </Form.Item>

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
          parentChannelId={parentChannelId}
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

export default TermEditWidget;
