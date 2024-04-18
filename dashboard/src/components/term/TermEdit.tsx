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
  html?: string;
  summary?: string;
  summary_is_community?: boolean;
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
  tags?: string[];
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
  tags,
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
  const [currChannel, setCurrChannel] = useState<ValueType[]>([]);
  const user = useAppSelector(_currentUser);

  const [form] = Form.useForm<ITerm>();
  const formRef = useRef<ProFormInstance>();
  useEffect(() => {
    if (word) {
      const url = `/v2/terms?view=word&word=${word}`;
      console.info("api request", url);
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

  let channelDisable = false;
  if (community) {
    channelDisable = true;
  }
  if (readonly) {
    channelDisable = true;
  }
  if (id) {
    channelDisable = true;
  }

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
          let copy_channel = "";
          if (values.copy_channel && values.copy_channel.length > 0) {
            copy_channel = values.copy_channel[values.copy_channel.length - 1];
          }
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
            const url = `/v2/terms?community_summary=1`;
            console.info("api request", url, newValue);
            res = await post<ITermDataRequest, ITermResponse>(url, newValue);
          } else {
            const url = `/v2/terms/${values.id}?community_summary=1`;
            console.info("api request", url, newValue);
            res = await put<ITermDataRequest, ITermResponse>(url, newValue);
          }
          console.debug("api response", res);

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
            tag: tags?.join(),
            meaning: "",
            meaning2: [],
            note: "",
            lang: "",
            copy_channel: [],
          };
          if (typeof id !== "undefined") {
            // 如果是编辑，就从服务器拉取数据。
            url = "/v2/terms/" + id;
            console.info("TermEdit is edit api request", url);
            const res = await get<ITermResponse>(url);
            console.debug("TermEdit is edit api response", res);
            if (res.ok) {
              let meaning2: string[] = [];
              if (res.data.other_meaning) {
                meaning2 = res.data.other_meaning.split(",");
              }

              let realChannelId: string | undefined = "";
              if (parentStudioId) {
                if (user?.id === parentStudioId) {
                  if (community) {
                    realChannelId = "";
                  } else {
                    realChannelId = res.data.channel?.id;
                  }
                } else {
                  realChannelId = parentChannelId;
                }
              } else {
                if (res.data.channel) {
                  realChannelId = res.data.channel?.id;
                  setCurrChannel([
                    {
                      label: res.data.channel?.name,
                      value: res.data.channel?.id,
                    },
                  ]);
                }
              }
              let copyToChannel: string[] = [];
              if (parentChannelId) {
                if (user?.roles?.includes("basic")) {
                  copyToChannel = [parentChannelId];
                } else {
                  copyToChannel = [""];
                }
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
                copy_channel: copyToChannel,
              };
              if (res.data.role === "reader" || res.data.role === "unknown") {
                setReadonly(true);
              }
            }
          } else if (typeof parentChannelId !== "undefined") {
            /**
             * 在channel新建
             * basic:仅保存在这个版本
             * pro: 默认studio通用
             */
            url = `/v2/terms?view=create-by-channel&channel=${parentChannelId}&word=${word}`;
            console.info("api request 在channel新建", url);
            const res = await get<ITermCreateResponse>(url);
            console.debug("api response", res);
            let channelId = "";
            let copyToChannel: string[] = [];
            if (user?.roles?.includes("basic")) {
              channelId = parentChannelId;
              copyToChannel = [parentChannelId];
            } else {
              channelId = user?.id === parentStudioId ? "" : parentChannelId;
              copyToChannel = [res.data.studio.id, parentChannelId];
            }
            data = {
              word: word ? word : "",
              tag: tags?.join(),
              meaning: "",
              meaning2: [],
              note: "",
              lang: res.data.language,
              channelId: channelId,
              copy_channel: copyToChannel,
            };
          } else if (typeof studioName !== "undefined") {
            //在studio新建

            url = `/v2/terms?view=create-by-studio&studio=${studioName}&word=${word}`;
            console.debug("在 studio 新建", url);
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
            name="channelId"
            allowClear
            label="版本(已经建立的术语，版本不可修改。可以选择另存为复制到另一个版本。)"
            width="md"
            placeholder={intl.formatMessage({
              id: "term.general-in-studio",
            })}
            disabled={channelDisable}
            options={[
              {
                value: "",
                label: intl.formatMessage({
                  id: "term.general-in-studio",
                }),
                disabled:
                  user?.id !== parentStudioId || user?.roles?.includes("basic"),
              },
              {
                value: parentChannelId ?? channelId,
                label: "仅用于此版本",
                disabled: !community && readonly,
              },
              ...currChannel,
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
                <LangSelect
                  disabled={hasChannel || channelDisable}
                  required={!hasChannel}
                />
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
            placeholder={intl.formatMessage({
              id: "term.general-in-studio",
            })}
            allowClear={user?.roles?.includes("basic") ? false : true}
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
          <ProFormCheckbox disabled name="pr">
            同时提交修改建议
          </ProFormCheckbox>
        </ProForm.Group>
      </ProForm>
    </>
  );
};

export default TermEditWidget;
