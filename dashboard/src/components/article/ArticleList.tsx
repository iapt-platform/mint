import { Link } from "react-router-dom";
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
import { ActionType, ProTable } from "@ant-design/pro-components";
import {
  PlusOutlined,
  DeleteOutlined,
  TeamOutlined,
  ExclamationCircleOutlined,
  FolderAddOutlined,
  ReconciliationOutlined,
} from "@ant-design/icons";

import ArticleCreate from "../../components/article/ArticleCreate";
import { delete_, get } from "../../request";
import {
  IArticleListResponse,
  IDeleteResponse,
} from "../../components/api/Article";
import { PublicityValueEnum } from "../../components/studio/table";
import { useEffect, useRef, useState } from "react";
import { ArticleTplModal } from "../template/Builder/ArticleTpl";
import Share, { EResType } from "../../components/share/Share";
import AddToAnthology from "../../components/article/AddToAnthology";
import AnthologySelect from "../../components/anthology/AnthologySelect";
import StudioName, { IStudio } from "../auth/Studio";
import { IUser } from "../../components/auth/User";
import { getSorterUrl } from "../../utils";
import TransferCreate from "../transfer/TransferCreate";
import { TransferOutLinedIcon } from "../../assets/icon";

const { Text } = Typography;

interface IArticleNumberResponse {
  ok: boolean;
  message: string;
  data: {
    my: number;
    collaboration: number;
  };
}

const renderBadge = (count: number, active = false) => {
  return (
    <Badge
      count={count}
      style={{
        marginBlockStart: -2,
        marginInlineStart: 4,
        color: active ? "#1890FF" : "#999",
        backgroundColor: active ? "#E6F7FF" : "#eee",
      }}
    />
  );
};

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
  onSelect?: Function;
}
const ArticleListWidget = ({
  studioName,
  multiple = true,
  editable = false,
  onSelect,
}: IWidget) => {
  const intl = useIntl(); //i18n
  const [openCreate, setOpenCreate] = useState(false);
  const [anthologyId, setAnthologyId] = useState<string>();
  const [activeKey, setActiveKey] = useState<React.Key | undefined>("my");
  const [myNumber, setMyNumber] = useState<number>(0);
  const [collaborationNumber, setCollaborationNumber] = useState<number>(0);
  const [transfer, setTransfer] = useState<string[]>();
  const [transferName, setTransferName] = useState<string>();
  const [transferOpen, setTransferOpen] = useState(false);
  const [pageSize, setPageSize] = useState(10);

  useEffect(() => {
    /**
     * 获取各种课程的数量
     */
    const url = `/v2/article-my-number?studio=${studioName}`;
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
        return delete_<IDeleteResponse>(`/v2/article/${id}`)
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
  const ref = useRef<ActionType>();

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
            tooltooltip: "过长会自动收缩",
            ellipsis: true,
            render: (text, row, index, action) => {
              return (
                <>
                  <div key={1}>
                    <Typography.Link
                      onClick={(
                        event: React.MouseEvent<HTMLElement, MouseEvent>
                      ) => {
                        if (typeof onSelect !== "undefined") {
                          onSelect(row.id, row.title, event);
                        }
                      }}
                    >
                      {row.title}
                    </Typography.Link>
                  </div>
                  <div key={2}>
                    <Text type="secondary">{row.subtitle}</Text>
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
              id: "columns.library.anthology.title",
            }),
            dataIndex: "subtitle",
            key: "subtitle",
            render: (text, row, index, action) => {
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
            tooltooltip: "过长会自动收缩",
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
            render: (text, row, index, action) => {
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
                          <ArticleTplModal
                            title={row.title}
                            type="article"
                            id={row.id}
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
        tableAlertRender={({
          selectedRowKeys,
          selectedRows,
          onCleanSelected,
        }) => (
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
        tableAlertOptionRender={({
          intl,
          selectedRowKeys,
          selectedRows,
          onCleanSelected,
        }) => {
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
        request={async (params = {}, sorter, filter) => {
          let url = `/v2/article?view=studio&view2=${activeKey}&name=${studioName}`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : pageSize);
          if (params.pageSize) {
            setPageSize(params.pageSize);
          }
          url += `&limit=${params.pageSize}&offset=${offset}`;
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
          showQuickJumper: true,
          showSizeChanger: true,
          pageSize: pageSize,
        }}
        search={false}
        options={{
          search: true,
        }}
        toolBarRender={() => [
          activeKey === "my" ? (
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
            activeKey,
            items: [
              {
                key: "my",
                label: (
                  <span>
                    {intl.formatMessage({ id: "labels.this-studio" })}
                    {renderBadge(myNumber, activeKey === "my")}
                  </span>
                ),
              },
              {
                key: "collaboration",
                label: (
                  <span>
                    {intl.formatMessage({ id: "labels.collaboration" })}
                    {renderBadge(
                      collaborationNumber,
                      activeKey === "collaboration"
                    )}
                  </span>
                ),
              },
            ],
            onChange(key) {
              console.log("show course", key);
              setActiveKey(key);
              setAnthologyId(undefined);
              ref.current?.reload();
            },
          },
        }}
      />

      <Modal
        destroyOnClose={true}
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
    </>
  );
};

export default ArticleListWidget;
