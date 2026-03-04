import { Link } from "react-router";
import { useIntl } from "react-intl";
import {
  Button,
  Popover,
  Dropdown,
  Typography,
  Modal,
  message,
  Space,
  Table,
  Badge,
} from "antd";
import { type ActionType, ProTable } from "@ant-design/pro-components";
import {
  PlusOutlined,
  DeleteOutlined,
  TeamOutlined,
  ExclamationCircleOutlined,
  FolderAddOutlined,
  ReconciliationOutlined,
} from "@ant-design/icons";

import ArticleCreate from "./ArticleCreate";
import { delete_, get } from "../../request";
import type { IArticleListResponse, IDeleteResponse } from "../../api/Article";
import { PublicityValueEnum } from "../studio/table";
import { useEffect, useRef, useState } from "react";

import Share from "../share/Share";

import AnthologySelect from "../anthology/AnthologySelect";
import StudioName from "../auth/Studio";

import { getSorterUrl } from "../../utils";
import TransferCreate from "../transfer/TransferCreate";
import { TransferOutLinedIcon } from "../../assets/icon";
import type { IStudio, IUser } from "../../api/Auth";
import { EResType } from "../share/utils";
import TplBuilder from "../tpl-builder/TplBuilder";
import AddToAnthology from "../anthology/AddToAnthology";
import StatusBadge from "../general/StatusBadge";
import ArticleDrawer from "./ArticleDrawer";

const { Text } = Typography;

interface IArticleNumberResponse {
  ok: boolean;
  message: string;
  data: {
    my: number;
    collaboration: number;
  };
}

interface DataItem {
  sn: number;
  id: string;
  title: string;
  subtitle: string;
  summary?: string | null;
  anthologyCount?: number;
  anthologyTitle?: string;
  publicity: number;
  studio?: IStudio;
  editor?: IUser;
  updated_at?: string;
}

interface IWidget {
  studioName?: string;
  editable?: boolean;
  multiple?: boolean;
  onSelect?: (
    id: string,
    title: string,
    event: React.MouseEvent<HTMLElement, MouseEvent>
  ) => void;
  // 受控参数（可选），不传则组件内部自治
  tab?: string;
  page?: number;
  pageSize?: number;
  onTabChange?: (tab: string) => void;
  onPageChange?: (page: number, pageSize: number) => void;
}

const ArticleList = ({
  studioName,
  multiple = true,
  editable = false,
  onSelect,
  tab,
  page,
  pageSize,
  onTabChange,
  onPageChange,
}: IWidget) => {
  const intl = useIntl(); //i18n
  const [openDrawer, setOpenOpenDrawer] = useState(false);
  const [currArticleId, setCurrArticleId] = useState<string>();
  const [openCreate, setOpenCreate] = useState(false);
  const [anthologyId, setAnthologyId] = useState<string>();
  const [myNumber, setMyNumber] = useState<number>(0);
  const [collaborationNumber, setCollaborationNumber] = useState<number>(0);
  const [transfer, setTransfer] = useState<string[]>();
  const [transferName, setTransferName] = useState<string>();
  const [transferOpen, setTransferOpen] = useState(false);

  // 受控/非受控：外部传入则用外部值，否则用内部 state
  const [internalTab, setInternalTab] = useState<string>("my");
  const [internalPage, setInternalPage] = useState<number>(1);
  const [internalPageSize, setInternalPageSize] = useState<number>(10);

  const currentTab = tab !== undefined ? tab : internalTab;
  const currentPage = page !== undefined ? page : internalPage;
  const currentPageSize = pageSize !== undefined ? pageSize : internalPageSize;

  const handleTabChange = (key: string) => {
    console.log("show course", key);
    if (onTabChange) {
      onTabChange(key);
    } else {
      setInternalTab(key);
      setInternalPage(1);
    }
    setAnthologyId(undefined);
  };

  const handlePageChange = (newPage: number, newPageSize: number) => {
    if (onPageChange) {
      onPageChange(newPage, newPageSize);
    } else {
      setInternalPage(newPage);
      setInternalPageSize(newPageSize);
    }
  };

  useEffect(() => {
    /**
     * 获取各种课程的数量
     */
    const url = `/api/v2/article-my-number?studio=${studioName}`;
    console.log("url", url);
    get<IArticleNumberResponse>(url).then((json) => {
      if (json.ok) {
        setMyNumber(json.data.my);
        setCollaborationNumber(json.data.collaboration);
      }
    });
  }, [studioName]);

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
      onOk() {
        console.log("delete", id);
        return delete_<IDeleteResponse>(`/api/v2/article/${id}`)
          .then((json) => {
            if (json.ok) {
              message.success("删除成功");
              ref.current?.reload();
            } else {
              message.error(json.message);
            }
          })
          .catch((e) => console.log("Oops errors!", e));
      },
    });
  };
  const ref = useRef<ActionType | null>(null);

  const [isModalOpen, setIsModalOpen] = useState(false);
  const [shareResId, setShareResId] = useState<string>("");
  const [shareResType, setShareResType] = useState<EResType>(EResType.article);
  const showShareModal = (resId: string, resType: EResType) => {
    setShareResId(resId);
    setShareResType(resType);
    setIsModalOpen(true);
  };

  const handleOk = () => {
    setIsModalOpen(false);
  };

  const handleCancel = () => {
    setIsModalOpen(false);
  };

  return (
    <>
      <ProTable<DataItem>
        actionRef={ref}
        // key 变化时强制重新挂载，使 defaultCurrent 重新生效
        // tab 切换或 pageSize 改变时都会重置到第1页
        key={`${currentTab}-${currentPageSize}`}
        // params 变化会自动触发 request，用于将 tab 传递给 request 函数
        params={{ tab: currentTab }}
        columns={[
          {
            title: intl.formatMessage({
              id: "dict.fields.sn.label",
            }),
            dataIndex: "sn",
            key: "sn",
            width: 50,
            search: false,
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.title.label",
            }),
            dataIndex: "title",
            key: "title",
            tooltip: "过长会自动收缩",
            ellipsis: true,
            render: (_text, row) => {
              return (
                <>
                  <div key={1}>
                    <Typography.Link
                      onClick={(
                        event: React.MouseEvent<HTMLElement, MouseEvent>
                      ) => {
                        if (onSelect) {
                          onSelect(row.id, row.title, event);
                        } else {
                          setOpenOpenDrawer(true);
                          setCurrArticleId(row.id);
                        }
                      }}
                    >
                      {row.title}
                    </Typography.Link>
                  </div>
                  <div key={2}>
                    <Text type="secondary">{row.subtitle}</Text>
                  </div>
                  {currentTab !== "my" ? (
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
              id: "columns.library.anthology.title",
            }),
            dataIndex: "subtitle",
            key: "subtitle",
            render: (_text, row) => {
              return (
                <Space>
                  {row.anthologyTitle}
                  {row.anthologyCount ? (
                    <Badge color="geekblue" count={row.anthologyCount} />
                  ) : undefined}
                </Space>
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
              id: "forms.fields.publicity.label",
            }),
            dataIndex: "publicity",
            key: "publicity",
            width: 100,
            search: false,
            filters: true,
            onFilter: true,
            valueEnum: PublicityValueEnum(),
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.updated-at.label",
            }),
            key: "updated_at",
            width: 100,
            search: false,
            dataIndex: "updated_at",
            valueType: "date",
            sorter: true,
          },
          {
            title: intl.formatMessage({ id: "buttons.option" }),
            key: "option",
            width: 120,
            valueType: "option",
            hideInTable: !editable,
            render: (_text, row, index) => {
              return [
                <Dropdown.Button
                  trigger={["click", "contextMenu"]}
                  key={index}
                  type="link"
                  menu={{
                    items: [
                      {
                        key: "tpl",
                        label: (
                          <TplBuilder
                            title={row.title}
                            tpl="article"
                            articleId={row.id}
                            trigger={<>模版</>}
                          />
                        ),
                        icon: <ReconciliationOutlined />,
                      },
                      {
                        key: "share",
                        label: intl.formatMessage({
                          id: "buttons.share",
                        }),
                        icon: <TeamOutlined />,
                      },
                      {
                        key: "addToAnthology",
                        label: (
                          <AddToAnthology
                            trigger={<Button type="link">加入文集</Button>}
                            studioName={studioName}
                            articleIds={[row.id]}
                          />
                        ),
                        icon: <FolderAddOutlined />,
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
                        case "share":
                          showShareModal(row.id, EResType.article);
                          break;
                        case "remove":
                          showDeleteConfirm(row.id, row.title);
                          break;
                        case "transfer":
                          setTransfer([row.id]);
                          setTransferName(row.title);
                          setTransferOpen(true);
                          break;
                        default:
                          break;
                      }
                    },
                  }}
                >
                  <Link
                    key={index}
                    to={`/article/article/${row.id}`}
                    target="_blank"
                  >
                    {intl.formatMessage({
                      id: "buttons.view",
                    })}
                  </Link>
                </Dropdown.Button>,
              ];
            },
          },
        ]}
        rowSelection={
          multiple
            ? {
                // 自定义选择项参考: https://ant.design/components/table-cn/#components-table-demo-row-selection-custom
                // 注释该行则默认不显示下拉选项
                selections: [Table.SELECTION_ALL, Table.SELECTION_INVERT],
              }
            : undefined
        }
        tableAlertRender={({ selectedRowKeys, onCleanSelected }) => (
          <Space size={24}>
            <span>
              {intl.formatMessage({ id: "buttons.selected" })}
              {selectedRowKeys.length}
              <Button type="link" onClick={onCleanSelected}>
                {intl.formatMessage({ id: "buttons.unselect" })}
              </Button>
            </span>
          </Space>
        )}
        tableAlertOptionRender={({ selectedRowKeys, onCleanSelected }) => {
          return (
            <Space>
              <Button
                type="link"
                onClick={() => {
                  const resId = selectedRowKeys.map((item) => item.toString());
                  setTransfer(resId);
                  setTransferName(resId.length + "个文章");
                  setTransferOpen(true);
                }}
              >
                转让
              </Button>
              <AddToAnthology
                studioName={studioName}
                trigger={<Button type="link">加入文集</Button>}
                articleIds={selectedRowKeys.map((item) => item.toString())}
                onFinally={() => {
                  onCleanSelected();
                }}
              />
            </Space>
          );
        }}
        request={async (params = {}, sorter) => {
          // tab 从 params 读取（由 ProTable 的 params prop 注入）
          const tab = params.tab ?? currentTab;
          let url = `/api/v2/article?view=studio&view2=${tab}&name=${studioName}`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : currentPageSize);
          url += `&limit=${params.pageSize ?? currentPageSize}&offset=${offset}`;
          url += params.keyword ? "&search=" + params.keyword : "";

          if (typeof anthologyId !== "undefined") {
            url += "&anthology=" + anthologyId;
          }

          url += getSorterUrl(sorter);
          console.log("url", url);
          const res = await get<IArticleListResponse>(url);
          const items: DataItem[] = res.data.rows.map((item, id) => {
            return {
              sn: id + offset + 1,
              id: item.uid,
              title: item.title,
              subtitle: item.subtitle,
              summary: item.summary,
              anthologyCount: item.anthology_count,
              anthologyTitle: item.anthology_first?.title,
              publicity: item.status,
              updated_at: item.updated_at,
              studio: item.studio,
              editor: item.editor,
            };
          });
          return {
            total: res.data.count,
            succcess: true,
            data: items,
          };
        }}
        rowKey="id"
        bordered
        pagination={{
          // 用 defaultCurrent / defaultPageSize（非受控）避免与 ProTable 内部状态冲突
          defaultCurrent: currentPage,
          defaultPageSize: currentPageSize,
          showQuickJumper: true,
          showSizeChanger: true,
        }}
        // 用 table 级别的 onChange 捕获分页事件，只在用户操作时触发一次，不会循环
        onChange={(pagination) => {
          handlePageChange(
            pagination.current ?? 1,
            pagination.pageSize ?? currentPageSize
          );
        }}
        search={false}
        options={{
          search: true,
        }}
        toolBarRender={() => [
          currentTab === "my" ? (
            <AnthologySelect
              studioName={studioName}
              onSelect={(value: string) => {
                setAnthologyId(value);
                ref.current?.reload();
              }}
            />
          ) : undefined,
          <Popover
            content={
              <ArticleCreate
                studio={studioName}
                anthologyId={anthologyId}
                onSuccess={() => {
                  setOpenCreate(false);
                  ref.current?.reload();
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
            activeKey: currentTab,
            items: [
              {
                key: "my",
                label: (
                  <span>
                    {intl.formatMessage({ id: "labels.this-studio" })}
                    <StatusBadge
                      count={myNumber}
                      active={currentTab === "my"}
                    />
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
                      active={currentTab === "collaboration"}
                    />
                  </span>
                ),
              },
            ],
            onChange(key) {
              handleTabChange(key as string);
            },
          },
        }}
      />

      <Modal
        destroyOnHidden={true}
        width={700}
        title={intl.formatMessage({ id: "labels.collaboration" })}
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
      >
        <Share resId={shareResId} resType={shareResType} />
      </Modal>

      <TransferCreate
        studioName={studioName}
        resId={transfer}
        resType="article"
        resName={transferName}
        open={transferOpen}
        onOpenChange={(visible: boolean) => setTransferOpen(visible)}
      />

      <ArticleDrawer
        articleId={currArticleId}
        type="article"
        open={openDrawer}
        onClose={() => {
          setCurrArticleId(undefined);
          setOpenOpenDrawer(false);
        }}
      />
    </>
  );
};

export default ArticleList;
