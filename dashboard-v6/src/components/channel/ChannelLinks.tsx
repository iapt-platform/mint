// channel 设置页「关联」页签：继承自、捆绑的逐词、降级版本、术语路径。
// 服务端只记录，降级拼接、术语取用以后由客户端实现（现在只做设置）。
import { useEffect, useRef, useState } from "react";
import { useIntl } from "react-intl";
import {
  Alert,
  Button,
  Form,
  List,
  Select,
  Skeleton,
  Space,
  Tag,
  Typography,
  message,
} from "antd";
import {
  ArrowDownOutlined,
  ArrowUpOutlined,
  DeleteOutlined,
} from "@ant-design/icons";

import {
  fetchChannel,
  fetchChannelLinks,
  fetchEditableChannels,
  fetchPublicChannels,
  updateChannelLinks,
  type ChannelLinks as ChannelLinksData,
  type ChannelRef,
  type EditableChannelQuery,
} from "../../api/channel";

const MAX_FALLBACK = 3;
const MAX_TERM_SOURCE = 10;

/** 组件内部用的 channel 摘要：字段齐全（生成的响应类型里字段都是可选的） */
interface Ref {
  id: string;
  name: string;
  type: string;
  lang: string;
}

interface LinksState {
  derived_from: Ref | null;
  wbw: Ref | null;
  fallback: Ref[];
  term_source: Ref[];
  can_update: boolean;
}

const toRef = (r: ChannelRef): Ref => ({
  id: r.id ?? "",
  name: r.name ?? "",
  type: r.type ?? "",
  lang: r.lang ?? "",
});

const toState = (links: ChannelLinksData): LinksState => ({
  derived_from: links.derived_from ? toRef(links.derived_from) : null,
  wbw: links.wbw ? toRef(links.wbw) : null,
  fallback: (links.fallback ?? []).map(toRef),
  term_source: (links.term_source ?? []).map(toRef),
  can_update: links.can_update ?? false,
});

/**
 * 候选列表按 channel 类型过滤：捆绑的逐词只列 wbw，术语路径只列 translation，
 * 降级版本只列与当前 channel 同类型的
 */
type PickType = NonNullable<EditableChannelQuery["type"]>;

/** 候选：我能写的 + 公开的 + 系统 channel，按名字搜，去重 */
const searchChannels = async (
  search: string,
  type?: PickType,
): Promise<Ref[]> => {
  const query = { search: search || undefined, type, per_page: 50 };
  const [editable, publicRows, system] = await Promise.all([
    fetchEditableChannels(query),
    fetchPublicChannels(query),
    fetchPublicChannels({ ...query, system: true }),
  ]);
  const seen = new Map<string, Ref>();
  for (const row of [
    ...(editable.data ?? []),
    ...(publicRows.data ?? []),
    ...(system.data ?? []),
  ]) {
    if (row.id && !seen.has(row.id)) {
      seen.set(row.id, toRef(row));
    }
  }
  return [...seen.values()];
};

const refLabel = (ref: Ref) => (
  <Space size={4} style={{ whiteSpace: "nowrap" }}>
    <span>{ref.name}</span>
    <Tag variant="filled">{ref.lang}</Tag>
    <Typography.Text type="secondary">{ref.type}</Typography.Text>
  </Space>
);

interface IPickerProps {
  value?: Ref | null;
  exclude: string[];
  type?: PickType;
  disabled?: boolean;
  allowClear?: boolean;
  placeholder?: string;
  onChange: (ref: Ref | null) => void;
}

/** 可搜索的 channel 下拉框（单选） */
const ChannelRefPicker = ({
  value,
  exclude,
  type,
  disabled,
  allowClear,
  placeholder,
  onChange,
}: IPickerProps) => {
  const [options, setOptions] = useState<Ref[]>([]);
  const [loading, setLoading] = useState(false);
  const timer = useRef<ReturnType<typeof setTimeout> | undefined>(undefined);

  const load = (search: string) => {
    clearTimeout(timer.current);
    timer.current = setTimeout(async () => {
      setLoading(true);
      try {
        setOptions(await searchChannels(search, type));
      } finally {
        setLoading(false);
      }
    }, 300);
  };

  const candidates = options.filter((o) => !exclude.includes(o.id));
  if (value && !candidates.some((o) => o.id === value.id)) {
    candidates.unshift(value);
  }

  return (
    <Select
      showSearch
      filterOption={false}
      allowClear={allowClear}
      disabled={disabled}
      placeholder={placeholder}
      loading={loading}
      style={{ width: 360, maxWidth: "100%" }}
      value={value?.id ?? null}
      onSearch={load}
      onOpenChange={(open) => open && options.length === 0 && load("")}
      onChange={(id: string | undefined) =>
        onChange(candidates.find((o) => o.id === id) ?? null)
      }
      options={candidates.map((o) => ({ value: o.id, label: refLabel(o) }))}
    />
  );
};

interface IListProps {
  value: Ref[];
  max: number;
  exclude: string[];
  type?: PickType;
  disabled?: boolean;
  onChange: (refs: Ref[]) => void;
}

/** 有序的 channel 列表：添加、上移、下移、移除 */
const OrderedChannelList = ({
  value,
  max,
  exclude,
  type,
  disabled,
  onChange,
}: IListProps) => {
  const intl = useIntl();
  const move = (from: number, to: number) => {
    const next = [...value];
    const [item] = next.splice(from, 1);
    next.splice(to, 0, item);
    onChange(next);
  };

  return (
    <Space orientation="vertical" style={{ width: "100%" }}>
      {value.length > 0 && (
        <List
          size="small"
          bordered
          style={{ maxWidth: 640 }}
          dataSource={value}
          renderItem={(ref, i) => (
            <List.Item
              actions={
                disabled
                  ? []
                  : [
                      <Button
                        key="up"
                        size="small"
                        type="text"
                        icon={<ArrowUpOutlined />}
                        disabled={i === 0}
                        onClick={() => move(i, i - 1)}
                      />,
                      <Button
                        key="down"
                        size="small"
                        type="text"
                        icon={<ArrowDownOutlined />}
                        disabled={i === value.length - 1}
                        onClick={() => move(i, i + 1)}
                      />,
                      <Button
                        key="remove"
                        size="small"
                        type="text"
                        danger
                        icon={<DeleteOutlined />}
                        onClick={() =>
                          onChange(value.filter((_, j) => j !== i))
                        }
                      />,
                    ]
              }
            >
              <Space>
                <Typography.Text
                  type="secondary"
                  style={{ whiteSpace: "nowrap" }}
                >
                  {i + 1}.
                </Typography.Text>
                {refLabel(ref)}
              </Space>
            </List.Item>
          )}
        />
      )}
      {!disabled && value.length < max && (
        <ChannelRefPicker
          value={null}
          exclude={[...exclude, ...value.map((r) => r.id)]}
          type={type}
          placeholder={intl.formatMessage({ id: "channel.links.add" })}
          onChange={(ref) => ref && onChange([...value, ref])}
        />
      )}
      {disabled && value.length === 0 && (
        <Typography.Text type="secondary">
          {intl.formatMessage({ id: "channel.links.empty" })}
        </Typography.Text>
      )}
    </Space>
  );
};

interface IWidget {
  channelId?: string;
}

const ChannelLinksWidget = ({ channelId }: IWidget) => {
  const intl = useIntl();
  const [links, setLinks] = useState<LinksState>();
  const [channelType, setChannelType] = useState<PickType>();
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    if (!channelId) {
      return;
    }
    Promise.all([fetchChannelLinks(channelId), fetchChannel(channelId)]).then(
      ([data, channel]) => {
        // channels.type 的取值与列表的 type 过滤参数是同一组（IndexChannelRequest::TYPES）
        setChannelType(channel?.type as PickType | undefined);
        if (data) {
          setLinks(toState(data));
        }
      },
    );
  }, [channelId]);

  if (!channelId || !links) {
    return <Skeleton active />;
  }

  const readonly = !links.can_update;
  const termSourceApplies = channelType === "translation";
  const self = [channelId];
  const set = (patch: Partial<LinksState>) => setLinks({ ...links, ...patch });
  const label = (key: string) =>
    intl.formatMessage({ id: `channel.links.${key}` });

  const save = async () => {
    setSaving(true);
    try {
      const saved = await updateChannelLinks(channelId, {
        derived_from: links.derived_from?.id ?? null,
        wbw: links.wbw?.id ?? null,
        fallback: links.fallback.map((r) => r.id),
        // 术语路径只对 translation channel 有效：别的类型不显示、也不提交，已有的数据不动
        ...(termSourceApplies
          ? { term_source: links.term_source.map((r) => r.id) }
          : {}),
      });
      if (saved) {
        setLinks(toState(saved));
      }
      message.success(label("saved"));
    } finally {
      setSaving(false);
    }
  };

  return (
    <Form layout="vertical" style={{ maxWidth: 640 }}>
      {readonly && (
        <Alert
          type="info"
          showIcon
          message={label("readonly")}
          style={{ marginBottom: 16 }}
        />
      )}
      <Form.Item
        label={label("derived_from")}
        tooltip={label("derived_from.tip")}
      >
        <ChannelRefPicker
          value={links.derived_from}
          exclude={self}
          allowClear
          disabled={readonly}
          placeholder={label("placeholder")}
          onChange={(ref) => set({ derived_from: ref })}
        />
      </Form.Item>
      <Form.Item label={label("wbw")} tooltip={label("wbw.tip")}>
        <ChannelRefPicker
          value={links.wbw}
          exclude={self}
          type="wbw"
          allowClear
          disabled={readonly}
          placeholder={label("placeholder")}
          onChange={(ref) => set({ wbw: ref })}
        />
      </Form.Item>
      <Form.Item label={label("fallback")} tooltip={label("fallback.tip")}>
        <OrderedChannelList
          value={links.fallback}
          max={MAX_FALLBACK}
          exclude={self}
          type={channelType}
          disabled={readonly}
          onChange={(refs) => set({ fallback: refs })}
        />
      </Form.Item>
      {termSourceApplies && (
        <Form.Item
          label={label("term_source")}
          tooltip={label("term_source.tip")}
        >
          <OrderedChannelList
            value={links.term_source}
            max={MAX_TERM_SOURCE}
            exclude={self}
            type="translation"
            disabled={readonly}
            onChange={(refs) => set({ term_source: refs })}
          />
        </Form.Item>
      )}
      {!readonly && (
        <Button type="primary" loading={saving} onClick={save}>
          {intl.formatMessage({ id: "buttons.save" })}
        </Button>
      )}
    </Form>
  );
};

export default ChannelLinksWidget;
