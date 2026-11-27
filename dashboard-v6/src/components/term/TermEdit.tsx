import { useIntl } from "react-intl";
import {
  ModalForm,
  ProForm,
  ProFormDependency,
  type ProFormInstance,
  ProFormSelect,
  ProFormText,
} from "@ant-design/pro-components";

import LangSelect from "../general/LangSelect";
import ChannelSelect from "../channel/ChannelSelect";
import {
  Alert,
  App,
  AutoComplete,
  Button,
  Dropdown,
  Form,
  Space,
  Spin,
  Tabs,
  Tag,
} from "antd";
import { DownOutlined } from "@ant-design/icons";
import {
  useEffect,
  useImperativeHandle,
  useRef,
  useState,
  type Ref,
  type RefObject,
} from "react";
import { useBlocker } from "react-router";
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
import ApiErrorResult from "../general/ApiErrorResult";

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
}

/** 另存为对话框的字段 */
interface ISaveAsValues {
  copy_channel?: string[];
  copy_lang?: string;
}

/** 有未保存修改时用户的选择 */
type LeaveChoice = "save" | "discard" | "stay";

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
  /** 拦截路由跳转与关闭 / 刷新标签页（独立编辑页用；Modal 里不开，一个 router 只能有一个 blocker） */
  blockNavigation?: boolean;
  onUpdate?: (data: Term) => void;
  onCancel?: () => void;
  /** 外层容器（如 Modal 的 ×、Esc）要关闭时调 `ref.current.cancel()`，走同一套未保存提示 */
  ref?: Ref<TermEditHandle>;
}

export interface TermEditHandle {
  /** 同点「取消」：有未保存的修改先提示，确认后调 onCancel / onUpdate */
  cancel: () => void;
}

/**
 * 有未保存修改时拦截离开：站内跳转弹出保存提示，关闭 / 刷新标签页走浏览器自带的提示。
 * 单独成组件，只在 `blockNavigation` 时挂载，免得 Modal 里的实例也注册 blocker。
 */
const LeaveGuard = ({
  dirtyRef,
  confirmLeave,
  save,
}: {
  dirtyRef: RefObject<boolean>;
  confirmLeave: () => Promise<LeaveChoice>;
  save: () => Promise<Term | undefined>;
}) => {
  const blocker = useBlocker(() => dirtyRef.current);
  const handlers = useRef({ confirmLeave, save });
  useEffect(() => {
    handlers.current = { confirmLeave, save };
  });

  useEffect(() => {
    if (blocker.state !== "blocked") {
      return;
    }
    handlers.current.confirmLeave().then(async (choice) => {
      if (choice === "stay") {
        blocker.reset();
        return;
      }
      if (choice === "save" && !(await handlers.current.save())) {
        blocker.reset();
        return;
      }
      dirtyRef.current = false;
      blocker.proceed();
    });
  }, [blocker, dirtyRef]);

  useEffect(() => {
    const onBeforeUnload = (e: BeforeUnloadEvent) => {
      if (dirtyRef.current) {
        e.preventDefault();
        e.returnValue = "";
      }
    };
    window.addEventListener("beforeunload", onBeforeUnload);
    return () => window.removeEventListener("beforeunload", onBeforeUnload);
  }, [dirtyRef]);

  return null;
};

/**
 * 术语编辑 / 新建（v3）。
 *
 * - 编辑：读到的 `etag` 随修改一起提交（If-Match）。这条在编辑期间被别人改过时后端返回 412，
 *   unwrap 弹出提示；不自动重试，避免覆盖别人的修改
 * - 词头与标签建立后不能改（引用方写 `[[word@tag]]`）；要改就新建一条，把旧的重定向过去
 * - 新建：选了版本就建在版本里（语言跟版本），选「通用于此 studio」就建 studio 级术语（要选语言）
 * - 分类写在正文的 `{{category|…}}` 里，由后端抽取
 * - 右上角「取消」「保存」，保存的下拉是「另存为」（弹出版本选择，复制成一条新术语）；
 *   两者完成后都调回调，由调用方决定去哪（编辑页回到预览）
 * - 有未保存的修改时，取消 / 离开会提示保存
 * - 编辑时先从服务器取这条术语；取不到（超时、网络不通、无权限…）只显示错误页与「重试」，
 *   不渲染表单——空表单看起来像是加载成功了，保存还会静默失败
 */
const TermEditWidget = ({
  id,
  word,
  tags,
  channelId,
  parentChannelId,
  parentStudioId,
  community = false,
  blockNavigation = false,
  onUpdate,
  onCancel,
  ref,
}: IWidget) => {
  const intl = useIntl();
  const [meaningOptions, setMeaningOptions] = useState<ValueType[]>([]);
  const [readonly, setReadonly] = useState(false);
  const [saveAsOpen, setSaveAsOpen] = useState(false);
  const [copyToChannel, setCopyToChannel] = useState<string[]>([]);
  const [saving, setSaving] = useState(false);
  const [dirty, setDirtyState] = useState(false);
  const dirtyRef = useRef(false);
  const [currChannel, setCurrChannel] = useState<ValueType[]>([]);
  const [etag, setEtag] = useState<string>();
  const [isGeneral, setIsGeneral] = useState(false);
  /** 表单初始值；undefined = 还在加载 */
  const [initialValues, setInitialValues] = useState<IFormValues>();
  const [loadError, setLoadError] = useState<unknown>();
  const [loadAttempt, setLoadAttempt] = useState(0);
  const user = useAppSelector(_currentUser);
  const { studioId: activeStudioId } = useActiveStudio();
  const { message, modal } = App.useApp();

  const [form] = Form.useForm<IFormValues>();
  const formRef = useRef<ProFormInstance | undefined>(undefined);
  const isEdit = typeof id !== "undefined" && !community;
  const label = (key: string) => intl.formatMessage({ id: key });

  const setDirty = (value: boolean) => {
    dirtyRef.current = value;
    setDirtyState(value);
  };

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

  /** 表单初始值：编辑时从服务器取，新建时按入口给默认值。失败就抛出（由调用处显示错误页） */
  const loadInitial = async (): Promise<IFormValues> => {
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
    };
    if (typeof id !== "undefined") {
      // 编辑：从服务器拉取。quiet：失败由下面的错误页显示，不再弹提示
      const term = await fetchTerm(id, {}, true);
      if (!term) {
        throw new Error(label("labels.route-error.other"));
      }
      setEtag(term.etag);
      setIsGeneral(!term.channel_id);
      if (term.channel) {
        setCurrChannel([
          { label: term.channel.name, value: term.channel.id ?? "" },
        ]);
      }
      if (parentChannelId) {
        setCopyToChannel(
          user?.roles?.includes("basic") ? [parentChannelId] : [""],
        );
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
      if (user?.roles?.includes("basic")) {
        target = parentChannelId;
        setCopyToChannel([parentChannelId]);
      } else {
        target = user?.id === parentStudioId ? "" : parentChannelId;
        setCopyToChannel([channel?.owner?.id ?? "", parentChannelId]);
      }
      data = {
        ...data,
        lang: channel?.lang ?? "",
        channelId: target,
      };
    } else if (channelId) {
      // 从版本的术语列表新建
      data = { ...data, channelId };
    }
    return data;
  };

  // 只在挂载与点「重试」时加载（同原来 ProForm request 的时机）；用 ref 取最新的 loadInitial
  const loadRef = useRef(loadInitial);
  useEffect(() => {
    loadRef.current = loadInitial;
  });
  useEffect(() => {
    let cancelled = false;
    setInitialValues(undefined);
    setLoadError(undefined);
    loadRef.current().then(
      (values) => {
        if (!cancelled) {
          setInitialValues(values);
        }
      },
      (e: unknown) => {
        if (!cancelled) {
          setLoadError(e);
        }
      },
    );
    return () => {
      cancelled = true;
    };
  }, [loadAttempt]);

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

  /**
   * 校验并保存。`saveAs` 给了就复制成新术语建到所选版本；否则编辑时改原条、新建时按「版本」字段建。
   * 校验不过（会自动切不到别的 tab，字段下面有红字）或保存失败返回 undefined。
   */
  const save = async (saveAs?: ISaveAsValues): Promise<Term | undefined> => {
    let values: IFormValues;
    try {
      values = await form.validateFields();
    } catch {
      return undefined;
    }
    if (!values.word || !values.meaning) {
      return undefined;
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

    setSaving(true);
    let saved: Term | undefined;
    try {
      if (isEdit && !saveAs && id) {
        if (!etag) {
          return undefined;
        }
        saved = await updateTerm(
          id,
          etag,
          isGeneral && values.lang
            ? { ...content, language: values.lang }
            : content,
        );
      } else {
        const target = saveAs
          ? (saveAs.copy_channel?.[saveAs.copy_channel.length - 1] ?? "")
          : (values.channelId ?? "");
        saved = await create(target, saveAs ? saveAs.copy_lang : values.lang, {
          ...content,
          meaning: values.meaning,
          word: values.word,
          tag: values.tag || null,
        });
      }
    } finally {
      setSaving(false);
    }
    if (!saved) {
      return undefined;
    }
    message.success(label("term.edit.saved"));
    setEtag(saved.etag);
    setDirty(false);
    store.dispatch(
      push({ word: saved.word ?? "", meaning: saved.meaning ?? "" }),
    );
    return saved;
  };

  /** 有未保存的修改时问用户：保存 / 不保存 / 继续编辑 */
  const confirmLeave = () =>
    new Promise<LeaveChoice>((resolve) => {
      const instance = modal.confirm({
        title: label("term.edit.unsaved.title"),
        content: label("term.edit.unsaved.content"),
        okText: label("buttons.save"),
        cancelText: label("term.edit.unsaved.keep"),
        onOk: () => resolve("save"),
        onCancel: () => resolve("stay"),
        footer: (_, { OkBtn, CancelBtn }) => (
          <>
            <CancelBtn />
            <Button
              danger
              onClick={() => {
                instance.destroy();
                resolve("discard");
              }}
            >
              {label("term.edit.unsaved.discard")}
            </Button>
            <OkBtn />
          </>
        ),
      });
    });

  const handleSave = async () => {
    const saved = await save();
    if (saved) {
      onUpdate?.(saved);
    }
  };

  const handleCancel = async () => {
    if (!dirtyRef.current) {
      onCancel?.();
      return;
    }
    const choice = await confirmLeave();
    if (choice === "save") {
      const saved = await save();
      if (saved) {
        onUpdate?.(saved);
      }
    } else if (choice === "discard") {
      setDirty(false);
      onCancel?.();
    }
  };

  useImperativeHandle(ref, () => ({ cancel: handleCancel }));

  if (loadError !== undefined) {
    return (
      <ApiErrorResult
        error={loadError}
        onRetry={() => setLoadAttempt((n) => n + 1)}
        extra={
          onCancel ? (
            <Button onClick={onCancel}>{label("buttons.back")}</Button>
          ) : undefined
        }
      />
    );
  }

  if (!initialValues) {
    return (
      <div style={{ padding: "50px 0", textAlign: "center" }}>
        <Spin />
      </div>
    );
  }

  return (
    <>
      {blockNavigation && (
        <LeaveGuard
          dirtyRef={dirtyRef}
          confirmLeave={confirmLeave}
          save={save}
        />
      )}
      <div
        style={{
          display: "flex",
          justifyContent: "flex-end",
          marginBottom: 12,
        }}
      >
        <Space>
          <Button onClick={handleCancel}>{label("buttons.cancel")}</Button>
          <Space.Compact>
            <Button
              type="primary"
              loading={saving}
              disabled={isEdit && readonly}
              onClick={handleSave}
            >
              {label("buttons.save")}
              {dirty ? " *" : ""}
            </Button>
            <Dropdown
              trigger={["click"]}
              menu={{
                items: [{ key: "save-as", label: label("term.edit.save-as") }],
                onClick: () => setSaveAsOpen(true),
              }}
            >
              <Button
                type="primary"
                icon={<DownOutlined />}
                disabled={saving}
              />
            </Dropdown>
          </Space.Compact>
        </Space>
      </div>
      {community ? (
        <Alert
          title="该资源为社区数据，您可以修改并保存到一个您有修改权限的版本中。"
          type="info"
          closable
        />
      ) : readonly ? (
        <Alert
          title="该资源为只读，如果需要修改，请联络拥有者分配权限。或者您可以用「另存为」将该术语保存到一个您有修改权限的版本中。"
          type="warning"
          closable
        />
      ) : undefined}
      <ProForm<IFormValues>
        form={form}
        formRef={formRef}
        autoFocusFirstInput={true}
        submitter={false}
        onValuesChange={() => setDirty(true)}
        initialValues={initialValues}
      >
        <Tabs
          items={[
            {
              key: "content",
              label: label("term.edit.tab.content"),
              forceRender: true,
              children: (
                <>
                  <ProForm.Group>
                    <ProFormText width="md" name="id" hidden />

                    <ProFormText
                      width="md"
                      name="word"
                      initialValue={word}
                      required
                      disabled={isEdit}
                      tooltip={
                        isEdit
                          ? label("term.fields.immutable.tooltip")
                          : undefined
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
                      fieldProps={{
                        mode: "tags",
                        tokenSeparators: [",", "，"],
                      }}
                      placeholder="Please select other meanings"
                      rules={[{ type: "array" }]}
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
                </>
              ),
            },
            {
              key: "settings",
              label: label("term.edit.tab.settings"),
              forceRender: true,
              children: (
                <>
                  <ProForm.Group>
                    <ProFormSelect
                      name="channelId"
                      allowClear
                      label={label("term.fields.channel.label")}
                      tooltip={label("term.edit.channel.tooltip")}
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
                            disabled={
                              hasChannel || (channelDisable && !isGeneral)
                            }
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
                      options={TERM_CONTENT_TYPES.map((t) => ({
                        value: t,
                        label: t,
                      }))}
                    />
                    <ProFormText
                      width="md"
                      name="redirect"
                      label={label("term.fields.redirect.label")}
                      tooltip={label("term.fields.redirect.tooltip")}
                    />
                  </ProForm.Group>
                </>
              ),
            },
          ]}
        />
      </ProForm>
      <ModalForm<ISaveAsValues>
        title={label("term.edit.save-as.title")}
        width={480}
        open={saveAsOpen}
        onOpenChange={setSaveAsOpen}
        modalProps={{ destroyOnHidden: true }}
        initialValues={{ copy_channel: copyToChannel }}
        onFinish={async (values) => {
          const saved = await save(values);
          if (!saved) {
            return false;
          }
          onUpdate?.(saved);
          return true;
        }}
      >
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
            const hasChannel =
              !!copy_channel &&
              copy_channel.length > 0 &&
              copy_channel[0] !== "";
            return (
              <LangSelect
                name="copy_lang"
                disabled={hasChannel}
                required={!hasChannel}
              />
            );
          }}
        </ProFormDependency>
      </ModalForm>
    </>
  );
};

export default TermEditWidget;
