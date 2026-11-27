import { type ActionType, ProTable } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { Link } from "react-router";
import { Alert, message, Modal, Progress, Typography } from "antd";
import type { SortOrder } from "antd/es/table/interface";
import { Button, Dropdown, Popover } from "antd";
import {
  PlusOutlined,
  ExclamationCircleOutlined,
  DeleteOutlined,
  TeamOutlined,
} from "@ant-design/icons";

import ChannelCreate from "./ChannelCreate";
import {
  channelOwnerStudio,
  channelRole,
  deleteChannel,
  fetchPublicChannels,
  fetchStudioChannels,
  type IChannel,
  type TChannelType,
} from "../../api/channel";
import { PublicityValueEnum } from "../studio/table";
import { useCallback, useEffect, useRef, useState } from "react";
import { loadPageSize, savePageSize } from "../../utils/pageSize";

/** 每页条数的本地记忆用这个列表名（工作区列表页与选版本弹窗共用） */
export const CHANNEL_LIST = "channel";
import type { IStudio, TRole } from "../../../src/api/Auth";
import ShareModal from "../share/ShareModal";

import StudioName from "../../../src/components/auth/Studio";
import StudioSelect from "./StudioSelect";

import TransferCreate from "../transfer/TransferCreate";
import { TransferOutLinedIcon } from "../../../src/assets/icon";
import { channelTypeFilter } from "./utils";
import StatusBadge from "../general/StatusBadge";
import { EResType } from "../share/utils";
import { useLoadGuard } from "../../hooks/useLoadGuard";
import ApiErrorResult from "../general/ApiErrorResult";

const { Text } = Typography;

export interface IResNumberResponse {
  ok: boolean;
  message: string;
  data: {
    my: number;
    collaboration: number;
  };
}

export interface IChapter {
  book: number;
  paragraph: number;
}

interface IChannelItem {
  id: number;
  uid: string;
  title: string;
  summary: string;
  type: TChannelType;
  role?: TRole;
  studio?: IStudio;
  publicity: number;
  progress?: number;
  created_at: string;
}

/** 列表的可重入状态：工作区列表页把它放进 URL，刷新（F5）后原样恢复 */
export interface IChannelListQuery {
  /** 页签：my 本工作室 / collaboration 协作 / community 社区 */
  tab: "my" | "collaboration" | "community";
  page: number;
  pageSize: number;
  /** 关键字 */
  q?: string;
  /** 协作页签里只看某个拥有者共享过来的 */
  collaborator?: string;
  order?: "created_at" | "updated_at" | "name" | "progress";
  dir?: "asc" | "desc";
}

interface IWidget {
  /**
   * 受控模式：传了 query + onQueryChange 就由外部（URL）保存页签、页码、搜索、排序；
   * 不传则组件自己记（选版本弹窗里用，不该改地址栏）。
   */
  query?: IChannelListQuery;
  onQueryChange?: (next: Partial<IChannelListQuery>) => void;
  /** v3：本人或团队的 uuid（列表、数量、新建都按它） */
  studioId?: string;
  /** 转让、协作者筛选仍走 v2，要 studio 名 */
  studioName?: string;
  type?: string;
  disableChannels?: string[];
  channelType?: TChannelType;
  chapter?: IChapter;
  onSelect?: (channel: IChannel) => void;
}

/** 列表排序：ProTable 的 sorter → v3 的 order / dir */
const toOrder = (sorter: Record<string, SortOrder>) => {
  const [field, order] = Object.entries(sorter).find(([, v]) => v) ?? [];
  return field
    ? {
        order: field as "created_at" | "updated_at" | "name" | "progress",
        dir: (order === "ascend" ? "asc" : "desc") as "asc" | "desc",
      }
    : {};
};

const ChannelTableWidget = ({
  query: controlledQuery,
  onQueryChange,
  studioId,
  studioName,
  disableChannels,
  channelType,
  chapter,
  onSelect,
}: IWidget) => {
  const load = useLoadGuard();
  const intl = useIntl();

  const [openCreate, setOpenCreate] = useState(false);

  // 不受控时组件自己记；受控时一律以外部 query 为准
  const [innerQuery, setInnerQuery] = useState<IChannelListQuery>(() => ({
    tab: "my",
    page: 1,
    pageSize: loadPageSize(CHANNEL_LIST),
  }));
  const query = controlledQuery ?? innerQuery;
  const changeQuery = (next: Partial<IChannelListQuery>) => {
    // 用户改了每页条数就记下来，下次打开沿用
    if (next.pageSize !== undefined && next.pageSize !== query.pageSize) {
      savePageSize(CHANNEL_LIST, next.pageSize);
    }
    if (onQueryChange) {
      onQueryChange(next);
    } else {
      setInnerQuery((prev) => ({ ...prev, ...next }));
    }
  };
  const activeKey = query.tab;
  // 搜索框的输入草稿：按回车才写进 query；query.q 变了（如从 URL 恢复、切页签）就跟着同步
  const [keywordDraft, setKeywordDraft] = useState(query.q ?? "");
  useEffect(() => setKeywordDraft(query.q ?? ""), [query.q]);
  const collaborator = query.collaborator;
  const [myNumber, setMyNumber] = useState<number>(0);
  const [collaborationNumber, setCollaborationNumber] = useState<number>(0);
  const [communityNumber, setCommunityNumber] = useState<number>(0);
  const [transfer, setTransfer] = useState<string[]>();
  const [transferName, setTransferName] = useState<string>();
  const [transferOpen, setTransferOpen] = useState(false);

  useEffect(() => {
    ref.current?.reload();
  }, [disableChannels]);

  /**
   * 三个页签的数量：各取一条，读 meta.total。条件与表格一致（带上 channelType），
   * 角标才会等于切过去后表格下方的「总共 N 条」（不含关键字：角标是页签的总量）。
   */
  const refreshNumbers = useCallback(() => {
    fetchPublicChannels({ type: channelType, per_page: 1 }).then((res) =>
      setCommunityNumber(res.meta?.total ?? 0),
    );
    if (!studioId) {
      return;
    }
    fetchStudioChannels(studioId, {
      relation: "own",
      type: channelType,
      per_page: 1,
    }).then((res) => setMyNumber(res.meta?.total ?? 0));
    fetchStudioChannels(studioId, {
      relation: "shared",
      type: channelType,
      per_page: 1,
    }).then((res) => setCollaborationNumber(res.meta?.total ?? 0));
  }, [studioId, channelType]);
  useEffect(refreshNumbers, [refreshNumbers]);

  const showDeleteConfirm = (id: string, title: string) => {
    Modal.confirm({
      icon: <ExclamationCircleOutlined />,
      title:
        intl.formatMessage({
          id: "message.delete.confirm",
        }) +
        intl.formatMessage({
          id: "message.irrevocable",
        }),

      content: title,
      okText: intl.formatMessage({
        id: "buttons.delete",
      }),
      okType: "danger",
      cancelText: intl.formatMessage({
        id: "buttons.no",
      }),
      async onOk() {
        // 有内容或无权删除时后端拒绝，原因由 unwrap 弹出
        await deleteChannel(id);
        message.success(intl.formatMessage({ id: "flashes.success" }));
        ref.current?.reload();
        refreshNumbers();
      },
    });
  };

  const ref = useRef<ActionType | null>(null);

  // 加载失败（超时、网络不通…）只显示错误页，不显示空列表
  if (load.error) {
    return <ApiErrorResult error={load.error} onRetry={load.retry} />;
  }

  return (
    <>
      {channelType ? (
        <Alert
          message={`仅显示版本类型${channelType}`}
          type="success"
          closable
        />
      ) : undefined}
      <ProTable<IChannelItem>
        onRequestError={load.onError}
        actionRef={ref}
        // 与文章列表同一套做法（ArticleList）：分页不受控，用 defaultCurrent / defaultPageSize；
        // 除页码外的状态一变就换 key 重新挂载，让新的默认值生效。受控的 current / pageSize 会和
        // ProTable 内部的分页状态互相纠正，URL 来回改、页面一直闪
        key={[
          // 切换个人 / 团队空间时 studioId 变，表格要按新身份重新取
          studioId,
          query.tab,
          query.pageSize,
          query.q,
          query.collaborator,
          query.order,
          query.dir,
          // 「重试」时重新挂载
          load.attempt,
        ].join("|")}
        columns={[
          {
            title: intl.formatMessage({
              id: "dict.fields.sn.label",
            }),
            dataIndex: "id",
            key: "id",
            width: 50,
            search: false,
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.title.label",
            }),
            dataIndex: "title",
            width: 250,
            key: "title",
            tooltip: "过长会自动收缩",
            ellipsis: true,
            render: (_text, row, index) => {
              return (
                <>
                  <div key={1}>
                    <Button
                      disabled={disableChannels?.includes(row.uid)}
                      type="link"
                      key={index}
                      onClick={() => {
                        if (typeof onSelect !== "undefined") {
                          const channel: IChannel = {
                            name: row.title,
                            id: row.uid,
                            type: row.type,
                          };
                          onSelect(channel);
                        }
                      }}
                    >
                      {row.title}
                    </Button>
                  </div>
                  {activeKey !== "my" ? (
                    <div key={3}>
                      <Text type="secondary">
                        <StudioName data={row.studio} />
                      </Text>
                    </div>
                  ) : undefined}
                </>
              );
            },
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.created-at.label",
            }),
            key: "progress",
            hideInTable: typeof chapter === "undefined",
            render(_dom, entity) {
              return (
                <Progress
                  size="small"
                  percent={Math.floor((entity.progress ?? 0) * 100)}
                  style={{ width: 150 }}
                />
              );
            },
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.summary.label",
            }),
            dataIndex: "summary",
            key: "summary",
            tooltip: "过长会自动收缩",
            ellipsis: true,
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.role.label",
            }),
            dataIndex: "role",
            key: "role",
            width: 80,
            search: false,
            filters: true,
            onFilter: true,
            valueEnum: {
              all: {
                text: intl.formatMessage({
                  id: "channel.type.all.title",
                }),
                status: "Default",
              },
              owner: {
                text: intl.formatMessage({
                  id: "auth.role.owner",
                }),
              },
              manager: {
                text: intl.formatMessage({
                  id: "auth.role.manager",
                }),
              },
              editor: {
                text: intl.formatMessage({
                  id: "auth.role.editor",
                }),
              },
              member: {
                text: intl.formatMessage({
                  id: "auth.role.member",
                }),
              },
            },
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.type.label",
            }),
            dataIndex: "type",
            key: "type",
            width: 80,
            search: false,
            filters: true,
            onFilter: true,
            valueEnum: channelTypeFilter(intl),
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.publicity.label",
            }),
            dataIndex: "publicity",
            key: "publicity",
            width: 80,
            search: false,
            filters: true,
            onFilter: true,
            valueEnum: PublicityValueEnum(),
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.created-at.label",
            }),
            key: "created_at",
            width: 100,
            search: false,
            dataIndex: "created_at",
            valueType: "date",
            sorter: true,
            defaultSortOrder:
              query.order === "created_at"
                ? query.dir === "asc"
                  ? "ascend"
                  : "descend"
                : undefined,
          },
          {
            title: intl.formatMessage({ id: "buttons.option" }),
            key: "option",
            width: 100,
            valueType: "option",
            hideInTable: activeKey !== "my",
            render: (_text, row, index) => {
              return [
                <Dropdown.Button
                  key={index}
                  type="link"
                  trigger={["click", "contextMenu"]}
                  menu={{
                    items: [
                      {
                        key: "share",
                        label: (
                          <ShareModal
                            trigger={intl.formatMessage({
                              id: "buttons.share",
                            })}
                            resId={row.uid}
                            resType={EResType.channel}
                          />
                        ),
                        icon: <TeamOutlined />,
                      },
                      {
                        key: "transfer",
                        label: intl.formatMessage({
                          id: "columns.studio.transfer.title",
                        }),
                        icon: <TransferOutLinedIcon />,
                      },
                      {
                        key: "remove",
                        label: intl.formatMessage({
                          id: "buttons.delete",
                        }),
                        icon: <DeleteOutlined />,
                        danger: true,
                      },
                    ],
                    onClick: (e) => {
                      switch (e.key) {
                        case "remove":
                          showDeleteConfirm(row.uid, row.title);
                          break;
                        case "transfer":
                          setTransfer([row.uid]);
                          setTransferName(row.title);
                          setTransferOpen(true);
                          break;
                        default:
                          break;
                      }
                    },
                  }}
                >
                  <Link to={`/workspace/channel/${row.uid}/setting`}>
                    {intl.formatMessage({
                      id: "buttons.setting",
                    })}
                  </Link>
                </Dropdown.Button>,
              ];
            },
          },
        ]}
        // 页签、页码、搜索、排序都在 query 里：它一变 ProTable 就重新请求
        // 只在用户真的翻页、改每页条数、点排序时触发一次，写回 query（受控时进 URL）
        onChange={(pagination, _filters, sorter) => {
          const one = Array.isArray(sorter) ? sorter[0] : sorter;
          const sort = one?.order
            ? toOrder({ [String(one.columnKey ?? one.field)]: one.order })
            : { order: undefined, dir: undefined };
          const pageSize = pagination.pageSize ?? query.pageSize;
          const sortChanged =
            sort.order !== query.order || sort.dir !== query.dir;
          // 每页条数或排序变了回到第 1 页
          const page =
            pageSize !== query.pageSize || sortChanged
              ? 1
              : (pagination.current ?? 1);
          if (
            page !== query.page ||
            pageSize !== query.pageSize ||
            sortChanged
          ) {
            changeQuery({ ...sort, page, pageSize });
          }
        }}
        request={async (params = {}) => {
          // 页码由 ProTable 管（初值来自 query 的 defaultCurrent），其余条件取自 query
          const page = params.current ?? query.page;
          const perPage = params.pageSize ?? query.pageSize;
          const listQuery = {
            search: query.q || undefined,
            type: channelType,
            book: chapter?.book,
            para: chapter?.paragraph,
            page,
            per_page: perPage,
            ...(chapter && activeKey === "community"
              ? { order: "progress" as const }
              : { order: query.order, dir: query.dir }),
          };
          try {
            const res =
              activeKey === "community"
                ? await fetchPublicChannels(listQuery)
                : studioId
                  ? await fetchStudioChannels(studioId, {
                      ...listQuery,
                      relation: activeKey === "my" ? "own" : "shared",
                      collaborator:
                        collaborator && collaborator !== "all"
                          ? collaborator
                          : undefined,
                    })
                  : undefined;
            const items: IChannelItem[] = (res?.data ?? []).map(
              (item, index) => ({
                id: (page - 1) * perPage + index + 1,
                uid: item.id ?? "",
                title: item.name ?? "",
                summary: item.summary ?? "",
                type: item.type as TChannelType,
                role: channelRole(item.abilities),
                progress: item.progress ?? undefined,
                studio: channelOwnerStudio(item.owner),
                publicity: item.status ?? 0,
                created_at: item.created_at ?? "",
              }),
            );
            return { total: res?.meta?.total ?? 0, success: true, data: items };
          } catch (e) {
            // 交给页面显示错误页（不是「暂无数据」）
            load.onError(e);
            return { total: 0, success: false, data: [] };
          }
        }}
        rowKey="id"
        bordered
        pagination={{
          defaultCurrent: query.page,
          defaultPageSize: query.pageSize,
          showQuickJumper: true,
          showSizeChanger: true,
        }}
        search={false}
        options={{
          search: {
            value: keywordDraft,
            onChange: (e: React.ChangeEvent<HTMLInputElement>) =>
              setKeywordDraft(e.target.value),
            // 关键字记进 query（受控时进 URL），返回 false 让 ProTable 不再自己存一份
            onSearch: (keyword: string) => {
              changeQuery({ q: keyword || undefined, page: 1 });
              return false;
            },
          },
        }}
        toolBarRender={() => [
          activeKey !== "my" ? (
            <StudioSelect
              studioName={studioName}
              value={collaborator}
              onSelect={(value: string) => {
                changeQuery({
                  collaborator: value && value !== "all" ? value : undefined,
                  page: 1,
                });
              }}
            />
          ) : undefined,
          <Popover
            content={
              <ChannelCreate
                studioId={studioId}
                onSuccess={() => {
                  setOpenCreate(false);
                  ref.current?.reload();
                  refreshNumbers();
                }}
              />
            }
            placement="bottomRight"
            trigger="click"
            open={openCreate}
            onOpenChange={(open: boolean) => {
              setOpenCreate(open);
            }}
          >
            <Button key="button" icon={<PlusOutlined />} type="primary">
              {intl.formatMessage({ id: "buttons.create" })}
            </Button>
          </Popover>,
        ]}
        toolbar={{
          menu: {
            activeKey,
            items: [
              {
                key: "my",
                label: (
                  <span>
                    {intl.formatMessage({ id: "labels.this-studio" })}
                    <StatusBadge count={myNumber} active={activeKey === "my"} />
                  </span>
                ),
              },
              {
                key: "collaboration",
                label: (
                  <span>
                    {intl.formatMessage({ id: "labels.collaboration" })}
                    <StatusBadge
                      count={collaborationNumber}
                      active={activeKey === "collaboration"}
                    />
                  </span>
                ),
              },
              {
                key: "community",
                label: (
                  <span>
                    {intl.formatMessage({ id: "labels.community" })}
                    <StatusBadge
                      count={communityNumber}
                      active={activeKey === "community"}
                    />
                  </span>
                ),
              },
            ],
            onChange(key) {
              changeQuery({
                tab: key as IChannelListQuery["tab"],
                collaborator: undefined,
                page: 1,
              });
            },
          },
        }}
      />
      <TransferCreate
        studioName={studioName}
        resId={transfer}
        resType="channel"
        resName={transferName}
        open={transferOpen}
        onOpenChange={(visible: boolean) => setTransferOpen(visible)}
      />
    </>
  );
};

export default ChannelTableWidget;
