import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormCheckbox,
  ProFormDependency,
  type ProFormInstance,
  ProFormSelect,
  ProFormSwitch,
  ProFormText,
} from "@ant-design/pro-components";

import LangSelect from "../general/LangSelect";
import ChannelSelect from "../channel/ChannelSelect";
import { Alert, App, AutoComplete, Form, Space, Tag } from "antd";
import { useEffect, useRef, useState } from "react";
import {
  createChannelTerm,
  createStudioTerm,
  fetchTerm,
  lookupTerms,
  TERM_CONTENT_TYPES,
  TERM_QUALITIES,
  updateTerm,
  type Term,
  type TermPatch,
} from "../../api/Term";
import { fetchChannel } from "../../api/channel";
import MDEditor from "@uiw/react-md-editor";
import { useAppSelector } from "../../hooks";
import { currentUser as _currentUser } from "../../reducers/current-user";
import { useActiveStudio } from "../../hooks/useActiveStudio";
import store from "../../store";
import { push } from "../../reducers/term-vocabulary";

interface ValueType {
  key?: string;
  label: React.ReactNode;
  value: string | number;
}

/** 表单字段 */
interface IFormValues {
  id?: string;
  word?: string;
  tag?: string;
  meaning?: string;
  meaning2?: string[];
  note?: string;
  summary?: string;
  quality?: string | null;
  content_type?: string;
  redirect?: string;
  channelId?: string;
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
  /** TermModal 用来显示「在 Studio 中打开」，本组件不用 */
  studioName?: string;
  channelId?: string;
  parentChannelId?: string;
  parentStudioId?: string;
  community?: boolean;
  onUpdate?: (data: Term) => void;
}

/**
 * 术语编辑 / 新建（v3）。
 *
 * - 编辑：读到的 `etag` 随修改一起提交（If-Match）。这条在编辑期间被别人改过时后端返回 412，
 *   unwrap 弹出提示；不自动重试，避免覆盖别人的修改
 * - 词头与标签建立后不能改（引用方写 `[[word@tag]]`）；要改就新建一条，把旧的重定向过去
 * - 新建：选了版本就建在版本里（语言跟版本），选「通用于此 studio」就建 studio 级术语（要选语言）
 * - 分类写在正文的 `{{category|…}}` 里，由后端抽取
 */
const TermEditWidget = ({
  id,
  word,
  tags,
  channelId,
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
  const [etag, setEtag] = useState<string>();
  const [isGeneral, setIsGeneral] = useState(false);
  const user = useAppSelector(_currentUser);
  const { studioId: activeStudioId } = useActiveStudio();
  const { message } = App.useApp();

  const [form] = Form.useForm<IFormValues>();
  const formRef = useRef<ProFormInstance | undefined>(undefined);
  const isEdit = typeof id !== "undefined" && !community;
  const label = (key: string) => intl.formatMessage({ id: key });

  useEffect(() => {
    if (!word) {
      return;
    }
    // 同词在各版本里的释义，按出现次数给出候选
    lookupTerms({ word, per_page: 200 }).then((json) => {
      const meaningMap = new Map<string, number>();
      for (const item of json.data ?? []) {
        if (item.meaning) {
          meaningMap.set(item.meaning, (meaningMap.get(item.meaning) ?? 0) + 1);
        }
      }
      const meaningList: ValueType[] = [];
      meaningMap.forEach((value, key) => {
        meaningList.push({
          value: key,
          label: (
            <Space style={{ display: "flex", justifyContent: "space-between" }}>
              {key}
              <Tag>{value}</Tag>
            </Space>
          ),
        });
      });
      setMeaningOptions(meaningList);
    });
  }, [word]);

  const channelDisable = community || readonly || typeof id !== "undefined";

  /** 新建：建在版本里，或建成 studio 级术语 */
  const create = async (
    target: string,
    language: string | undefined,
    body: TermPatch & { word: string; tag?: string | null; meaning: string },
  ): Promise<Term | undefined> => {
    if (target) {
      return createChannelTerm(target, body);
    }
    const studio = parentStudioId ?? activeStudioId ?? user?.id;
    if (!studio) {
      return undefined;
    }
    return createStudioTerm(studio, { ...body, language: language ?? "" });
  };

  return (
    <>
      {community ? (
        <Alert
          title="该资源为社区数据，您可以修改并保存到一个您有修改权限的版本中。"
          type="info"
          closable
        />
      ) : readonly ? (
        <Alert
          title="该资源为只读，如果需要修改，请联络拥有者分配权限。或者您可以在下面的版本选择中选择另一个版本，将该术语保存到一个您有修改权限的版本中。"
          type="warning"
          closable
        />
      ) : undefined}
      <ProForm<IFormValues>
        form={form}
        formRef={formRef}
        autoFocusFirstInput={true}
        onFinish={async (values: IFormValues) => {
          if (!values.word || !values.meaning) {
            return;
          }
          const content: TermPatch = {
            meaning: values.meaning,
            other_meaning: values.meaning2?.join() || null,
            note: values.note || null,
            summary: values.summary || null,
            quality: (values.quality || null) as TermPatch["quality"],
            content_type: values.content_type as TermPatch["content_type"],
            redirect: values.redirect || null,
          };

          let saved: Term | undefined;
          if (isEdit && !values.save_as && id) {
            if (!etag) {
              return;
            }
            saved = await updateTerm(
              id,
              etag,
              isGeneral && values.lang
                ? { ...content, language: values.lang }
                : content,
            );
          } else {
            const target = values.save_as
              ? (values.copy_channel?.[values.copy_channel.length - 1] ?? "")
              : (values.channelId ?? "");
            saved = await create(
              target,
              values.save_as ? values.copy_lang : values.lang,
              {
                ...content,
                meaning: values.meaning,
                word: values.word,
                tag: values.tag || null,
              },
            );
          }
          if (!saved) {
            return;
          }
          message.success("提交成功");
          setEtag(saved.etag);
          store.dispatch(
            push({ word: saved.word ?? "", meaning: saved.meaning ?? "" }),
          );
          onUpdate?.(saved);
          return true;
        }}
        request={async () => {
          let data: IFormValues = {
            word: word ? word : "",
            tag: tags?.join(),
            meaning: "",
            meaning2: [],
            note: "",
            summary: "",
            quality: null,
            content_type: TERM_CONTENT_TYPES[0],
            redirect: "",
            lang: "",
            copy_channel: [],
          };
          if (typeof id !== "undefined") {
            // 编辑：从服务器拉取
            const term = await fetchTerm(id);
            if (!term) {
              return data;
            }
            setEtag(term.etag);
            setIsGeneral(!term.channel_id);
            if (term.channel) {
              setCurrChannel([
                { label: term.channel.name, value: term.channel.id ?? "" },
              ]);
            }
            let copyToChannel: string[] = [];
            if (parentChannelId) {
              copyToChannel = user?.roles?.includes("basic")
                ? [parentChannelId]
                : [""];
            }
            data = {
              id: community ? undefined : term.id,
              word: term.word,
              tag: term.tag ?? undefined,
              meaning: term.meaning,
              meaning2: term.other_meaning ? term.other_meaning.split(",") : [],
              note: term.note ?? "",
              summary: term.summary ?? "",
              quality: term.quality ?? null,
              content_type: term.content_type,
              redirect: term.redirect ?? "",
              lang: term.language,
              channelId: community
                ? (parentChannelId ?? "")
                : (term.channel_id ?? ""),
              copy_channel: copyToChannel,
            };
            if (!community && !term.abilities?.update) {
              setReadonly(true);
            }
          } else if (typeof parentChannelId !== "undefined") {
            /**
             * 在 channel 里新建
             * basic：只能建在这个版本
             * pro：在自己的 studio 里默认建成通用术语，在别人的版本里建在版本里
             */
            const channel = await fetchChannel(parentChannelId);
            let target = "";
            let copyToChannel: string[] = [];
            if (user?.roles?.includes("basic")) {
              target = parentChannelId;
              copyToChannel = [parentChannelId];
            } else {
              target = user?.id === parentStudioId ? "" : parentChannelId;
              copyToChannel = [channel?.owner?.id ?? "", parentChannelId];
            }
            data = {
              ...data,
              lang: channel?.lang ?? "",
              channelId: target,
              copy_channel: copyToChannel,
            };
          } else if (channelId) {
            // 从版本的术语列表新建
            data = { ...data, channelId };
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
            disabled={isEdit}
            tooltip={
              isEdit ? label("term.fields.immutable.tooltip") : undefined
            }
            label={label("term.fields.word.label")}
            rules={[{ required: true }]}
            fieldProps={{ showCount: true, maxLength: 128 }}
          />
          <ProFormText
            width="md"
            name="tag"
            disabled={isEdit}
            tooltip={
              isEdit
                ? label("term.fields.immutable.tooltip")
                : label("term.fields.description.tooltip")
            }
            label={label("term.fields.description.label")}
            rules={[{ pattern: /^[^@]*$/, message: "@" }]}
          />
        </ProForm.Group>
        <ProForm.Group>
          <ProForm.Item
            width="md"
            name="meaning"
            label={label("term.fields.meaning.label")}
            rules={[{ required: true }]}
          >
            <AutoComplete
              options={meaningOptions}
              style={{ width: "328px" }}
              allowClear
              maxLength={128}
            />
          </ProForm.Item>

          <ProFormSelect
            width="md"
            name="meaning2"
            label={label("term.fields.meaning2.label")}
            fieldProps={{ mode: "tags", tokenSeparators: [",", "，"] }}
            placeholder="Please select other meanings"
            rules={[{ type: "array" }]}
          />
        </ProForm.Group>
        <ProForm.Group>
          <ProFormSelect
            name="channelId"
            allowClear
            label="版本(已经建立的术语，版本不可修改。可以选择另存为复制到另一个版本。)"
            width="md"
            placeholder={label("term.general-in-studio")}
            disabled={channelDisable}
            options={[
              {
                value: "",
                label: label("term.general-in-studio"),
                disabled:
                  (parentStudioId !== undefined &&
                    user?.id !== parentStudioId) ||
                  user?.roles?.includes("basic"),
              },
              ...((parentChannelId ?? channelId)
                ? [
                    {
                      value: parentChannelId ?? channelId ?? "",
                      label: "仅用于此版本",
                      disabled: !community && readonly,
                    },
                  ]
                : []),
              ...currChannel,
            ]}
          />

          <ProFormDependency name={["channelId"]}>
            {({ channelId }) => {
              const hasChannel = !!channelId;
              return (
                <LangSelect
                  disabled={hasChannel || (channelDisable && !isGeneral)}
                  required={!hasChannel}
                />
              );
            }}
          </ProFormDependency>
        </ProForm.Group>
        <ProForm.Group>
          <ProFormSelect
            width="sm"
            name="quality"
            allowClear
            label={label("term.fields.quality.label")}
            placeholder={label("term.fields.quality.pending")}
            options={TERM_QUALITIES.map((q) => ({
              value: q,
              label: label(`term.fields.quality.${q}`),
            }))}
          />
          <ProFormSelect
            width="sm"
            name="content_type"
            label={label("term.fields.content_type.label")}
            allowClear={false}
            options={TERM_CONTENT_TYPES.map((t) => ({ value: t, label: t }))}
          />
          <ProFormText
            width="md"
            name="redirect"
            label={label("term.fields.redirect.label")}
            tooltip={label("term.fields.redirect.tooltip")}
          />
        </ProForm.Group>
        <ProForm.Group>
          <Form.Item
            style={{ width: "100%" }}
            name="summary"
            label={label("term.fields.summary.label")}
            tooltip={label("term.fields.summary.tooltip")}
          >
            <MDEditor height={120} preview="edit" />
          </Form.Item>
        </ProForm.Group>
        <ProForm.Group>
          <Form.Item
            style={{ width: "100%" }}
            name="note"
            label={label("forms.fields.note.label")}
          >
            <MDEditor />
          </Form.Item>
        </ProForm.Group>
        <ProForm.Group>
          <ProFormSwitch
            name="save_as"
            label="另存为"
            fieldProps={{
              onChange: (checked: boolean) => {
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
            placeholder={label("term.general-in-studio")}
            allowClear={user?.roles?.includes("basic") ? false : true}
            tooltip={label("term.fields.channel.tooltip")}
            label={label("term.fields.channel.label")}
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
