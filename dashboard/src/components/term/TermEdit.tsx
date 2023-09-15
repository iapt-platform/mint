import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormCheckbox,
  ProFormDependency,
  ProFormInstance,
  ProFormSelect,
  ProFormSwitch,
  ProFormText,
} from "@ant-design/pro-components";

import LangSelect from "../general/LangSelect";
import ChannelSelect from "../channel/ChannelSelect";
import {
  Alert,
  AutoComplete,
  Button,
  Form,
  Input,
  message,
  Space,
  Tag,
} from "antd";
import { useEffect, useRef, useState } from "react";
import {
  ITermCreateResponse,
  ITermDataRequest,
  ITermListResponse,
  ITermResponse,
} from "../api/Term";
import { get, post, put } from "../../request";
import MDEditor from "@uiw/react-md-editor";
import { useAppSelector } from "../../hooks";
import { currentUser as _currentUser } from "../../reducers/current-user";
import store from "../../store";
import { push } from "../../reducers/term-vocabulary";

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
  channelId?: string;
  studioId?: string;
  lang?: string;
  save_as?: boolean;
  copy_channel?: string[];
  copy_lang?: string;
  pr?: boolean;
}

interface IWidget {
  id?: string;
  word?: string;
  studioName?: string;
  channelId?: string;
  parentChannelId?: string;
  parentStudioId?: string;
  community?: boolean;
  onUpdate?: Function;
}
const TermEditWidget = ({
  id,
  word,
  channelId,
  studioName,
  parentChannelId,
  parentStudioId,
  community = false,
  onUpdate,
}: IWidget) => {
  const intl = useIntl();
  const [meaningOptions, setMeaningOptions] = useState<ValueType[]>([]);
  const [readonly, setReadonly] = useState(false);
  const [isSaveAs, setIsSaveAs] = useState(false);
  const user = useAppSelector(_currentUser);

  //console.log("word", id, word, channelId, studioName);

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
    <>
      {community ? (
        <Alert
          message="该资源为社区数据，您可以修改并保存到一个您有修改权限的版本中。"
          type="info"
          closable
          action={
            <Button disabled size="small" type="text">
              详情
            </Button>
          }
        />
      ) : readonly ? (
        <Alert
          message="该资源为只读，如果需要修改，请联络拥有者分配权限。或者您可以在下面的版本选择中选择另一个版本，将该术语保存到一个您有修改权限的版本中。"
          type="warning"
          closable
          action={
            <Button disabled size="small" type="text">
              详情
            </Button>
          }
        />
      ) : undefined}
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
          const copy_channel = values.copy_channel
            ? values.copy_channel[values.copy_channel.length - 1]
              ? values.copy_channel[values.copy_channel.length - 1]
              : ""
            : "";
          const newValue = {
            id: values.id,
            word: values.word,
            tag: values.tag,
            meaning: values.meaning,
            other_meaning: values.meaning2?.join(),
            note: values.note,
            channel: values.save_as ? copy_channel : values.channelId,
            parent_channel_id: parentChannelId,
            studioName: studioName,
            studioId: parentStudioId,
            language: values.save_as ? values.copy_lang : values.lang,
            pr: values.save_as ? values.pr : undefined,
          };
          console.log("value", newValue);
          let res: ITermResponse;
          if (typeof values.id === "undefined" || community || values.save_as) {
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
            store.dispatch(
              push({ word: res.data.word, meaning: res.data.meaning })
            );
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
            word: word ? word : "",
            tag: "",
            meaning: "",
            meaning2: [],
            note: "",
            lang: "",
            copy_channel: [],
          };
          if (typeof id !== "undefined") {
            // 如果是编辑，就从服务器拉取数据。
            url = "/v2/terms/" + id;
            console.log("有id", url);
            const res = await get<ITermResponse>(url);
            if (res.ok) {
              let meaning2: string[] = [];
              if (res.data.other_meaning) {
                meaning2 = res.data.other_meaning.split(",");
              }

              let realChannelId: string | undefined = "";
              if (user?.id === parentStudioId) {
                if (community) {
                  realChannelId = "";
                } else {
                  realChannelId = res.data.channel?.id;
                }
              } else {
                realChannelId = parentChannelId;
              }

              data = {
                id: res.data.guid,
                word: res.data.word,
                tag: res.data.tag,
                meaning: res.data.meaning,
                meaning2: meaning2,
                note: res.data.note ? res.data.note : "",
                lang: res.data.language,
                channelId: realChannelId,
                copy_channel: res.data.channel
                  ? [res.data.studio.id, res.data.channel?.id]
                  : undefined,
              };
              if (res.data.role === "reader" || res.data.role === "unknown") {
                setReadonly(true);
              }
            }
          } else if (typeof parentChannelId !== "undefined") {
            //在channel新建
            url = `/v2/terms?view=create-by-channel&channel=${parentChannelId}&word=${word}`;
            console.log("在channel新建", url);
            const res = await get<ITermCreateResponse>(url);
            console.log(res);
            data = {
              word: word ? word : "",
              tag: "",
              meaning: "",
              meaning2: [],
              note: "",
              lang: res.data.language,
              channelId: user?.id === parentStudioId ? "" : parentChannelId,
              copy_channel: [res.data.studio.id, parentChannelId],
            };
          } else if (typeof studioName !== "undefined") {
            //在studio新建
            url = `/v2/terms?view=create-by-studio&studio=${studioName}&word=${word}`;
            console.log("在 studio 新建", url);
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
          <ProForm.Item
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
              <Input width="md" allowClear showCount={true} />
            </AutoComplete>
          </ProForm.Item>

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
          <ProFormSelect
            initialValue={user?.id === parentStudioId ? "" : parentChannelId}
            name="channelId"
            allowClear
            label="版本"
            width="md"
            placeholder="通用于此Studio"
            disabled={
              (!community && readonly) ||
              (!community && typeof id !== "undefined")
            }
            options={[
              {
                value: "",
                label: "通用于我的Studio",
                disabled: user?.id !== parentStudioId,
              },
              {
                value: parentChannelId,
                label: "仅用于此版本",
                disabled: !community && readonly,
              },
            ]}
          />
          <ProFormDependency name={["channelId"]}>
            {({ channelId }) => {
              const hasChannel = channelId
                ? channelId === ""
                  ? false
                  : true
                : false;
              return (
                <LangSelect disabled={hasChannel} required={!hasChannel} />
              );
            }}
          </ProFormDependency>
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
        <ProForm.Group>
          <ProFormSwitch
            name="save_as"
            label="另存为"
            fieldProps={{
              onChange: (
                checked: boolean,
                event: React.MouseEvent<HTMLButtonElement, MouseEvent>
              ) => {
                setIsSaveAs(checked);
              },
            }}
          />
        </ProForm.Group>
        <ProForm.Group style={{ display: isSaveAs ? "block" : "none" }}>
          <ChannelSelect
            channelId={channelId}
            parentChannelId={parentChannelId}
            parentStudioId={parentStudioId}
            width="md"
            name="copy_channel"
            placeholder="通用于此Studio"
            tooltip={intl.formatMessage({
              id: "term.fields.channel.tooltip",
            })}
            label={intl.formatMessage({
              id: "term.fields.channel.label",
            })}
          />
          <ProFormDependency name={["copy_channel"]}>
            {({ copy_channel }) => {
              const hasChannel = copy_channel
                ? copy_channel.length === 0 || copy_channel[0] === ""
                  ? false
                  : true
                : false;
              return (
                <LangSelect
                  name="copy_lang"
                  disabled={hasChannel}
                  required={isSaveAs && !hasChannel}
                />
              );
            }}
          </ProFormDependency>
        </ProForm.Group>
        <ProForm.Group style={{ display: isSaveAs ? "block" : "none" }}>
          <ProFormCheckbox name="pr">同时提交修改建议</ProFormCheckbox>
        </ProForm.Group>
      </ProForm>
    </>
  );
};

export default TermEditWidget;
