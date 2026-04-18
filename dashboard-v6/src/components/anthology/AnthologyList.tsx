import { type ActionType, ProTable } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { Link } from "react-router";
import { message, Modal, Typography } from "antd";
import { PlusOutlined } from "@ant-design/icons";
import { Button, Dropdown, Popover } from "antd";
import {
  ExclamationCircleOutlined,
  TeamOutlined,
  DeleteOutlined,
  EyeOutlined,
} from "@ant-design/icons";

import AnthologyCreate from "./AnthologyCreate";
import type {
  IAnthologyListResponse,
  IDeleteResponse,
} from "../../api/article";
import { delete_, get } from "../../request";
import { PublicityValueEnum } from "../studio/table";
import { useEffect, useRef, useState } from "react";

import { type IResNumberResponse } from "../channel/ChannelTable";
import { fullUrl, getSorterUrl } from "../../utils";
import type { IStudio } from "../../api/Auth";
import { EResType } from "../share/utils";
import Studio from "../auth/Studio";
import StatusBadge from "../general/StatusBadge";
import Share from "../share/Share";

const { Text } = Typography;

interface IItem {
  sn: number;
  id: string;
  title: string;
  subtitle: string;
  publicity: number;
  articles: number;
  studio?: IStudio;
  updated_at: string;
}
interface IWidget {
  title?: string;
  studioName?: string;
  showCol?: string[];
  showCreate?: boolean;
  showOption?: boolean;
  onTitleClick?: (id: string) => void;
  // 受控参数（可选），不传则组件内部自治
  tab?: string;
  page?: number;
  pageSize?: number;
  onTabChange?: (tab: string) => void;
  onPageChange?: (page: number, pageSize: number) => void;
}
const AnthologyListWidget = ({
  title,
  studioName,
  showCreate = true,
  showOption = true,
  onTitleClick,
  tab,
  page,
  pageSize,
  onTabChange,
  onPageChange,
}: IWidget) => {
  const intl = useIntl();
  const [openCreate, setOpenCreate] = useState(false);

  // 受控/非受控：外部传入则用外部值，否则用内部 state
  const [internalTab, setInternalTab] = useState<string>("my");
  const [internalPage, setInternalPage] = useState<number>(1);
  const [internalPageSize, setInternalPageSize] = useState<number>(10);

  const currentTab = tab !== undefined ? tab : internalTab;
  const currentPage = page !== undefined ? page : internalPage;
  const currentPageSize = pageSize !== undefined ? pageSize : internalPageSize;

  const [myNumber, setMyNumber] = useState<number>(0);
  const [collaborationNumber, setCollaborationNumber] = useState<number>(0);

  useEffect(() => {
    const url = `/api/v2/anthology-my-number?studio=${studioName}`;
    console.log("url", url);
    get<IResNumberResponse>(url).then((json) => {
      if (json.ok) {
        setMyNumber(json.data.my);
        setCollaborationNumber(json.data.collaboration);
      }
    });
  }, [studioName]);

  const handleTabChange = (key: string) => {
    console.log("show course", key);
    // 切 tab 时重置页码到第1页（由 key 变化触发 ProTable 重新挂载来实现）
    if (onTabChange) {
      onTabChange(key);
    } else {
      setInternalTab(key);
      setInternalPage(1); // 非受控模式下手动重置
    }
    // 注意：不需要 ref.current?.reload()，params 变化会自动触发
  };

  const handlePageChange = (newPage: number, newPageSize: number) => {
    if (onPageChange) {
      onPageChange(newPage, newPageSize);
    } else {
      setInternalPage(newPage);
      setInternalPageSize(newPageSize);
    }
  };

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
        return delete_<IDeleteResponse>(`/api/v2/anthology/${id}`)
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

  const [isModalOpen, setIsModalOpen] = useState(false);
  const [shareResId, setShareResId] = useState<string>("");
  const [shareResType, setShareResType] = useState<EResType>(
    EResType.collection
  );
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

  const ref = useRef<ActionType | null>(null);
  return (
    <>
      <ProTable<IItem>
        headerTitle={title}
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
            render: (_text, row, index) => {
              return (
                <div key={index}>
                  <div>
                    <Typography.Link
                      onClick={() => {
                        if (typeof onTitleClick !== "undefined") {
                          onTitleClick(row.id);
                        }
                      }}
                    >
                      {row.title}
                    </Typography.Link>
                  </div>
                  <Text type="secondary">{row.subtitle}</Text>
                </div>
              );
            },
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.owner.label",
            }),
            dataIndex: "studio",
            key: "studio",
            render: (_text, row) => {
              return <Studio data={row.studio} />;
            },
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
              id: "article.fields.article.count.label",
            }),
            dataIndex: "articles",
            key: "articles",
            width: 100,
            search: false,
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
            hideInTable: !showOption,
            valueType: "option",
            render: (_text, row, index) => [
              <Dropdown.Button
                key={index}
                type="link"
                trigger={["click", "contextMenu"]}
                menu={{
                  items: [
                    {
                      key: "open",
                      label: (
                        <Link to={`/anthology/${row.id}`}>
                          {intl.formatMessage({
                            id: "buttons.open.in.library",
                          })}
                        </Link>
                      ),
                      icon: <EyeOutlined />,
                    },
                    {
                      key: "share",
                      label: intl.formatMessage({
                        id: "buttons.share",
                      }),
                      icon: <TeamOutlined />,
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
                      case "open":
                        window.open(fullUrl(`/anthology/${row.id}`), "_blank");
                        break;
                      case "share":
                        console.log("share");
                        showShareModal(row.id, EResType.collection);
                        break;
                      case "remove":
                        showDeleteConfirm(row.id, row.title);
                        break;
                    }
                  },
                }}
              >
                <Link to={`/anthology/${row.id}`} target="_blank">
                  {intl.formatMessage({
                    id: "buttons.view",
                  })}
                </Link>
              </Dropdown.Button>,
            ],
          },
        ]}
        request={async (params = {}, sorter, filter) => {
          console.log(params, sorter, filter);
          // tab 从 params 读取（由 ProTable 的 params prop 注入）
          // current 和 pageSize 由 ProTable 内部管理，直接从 params 读
          const tab = params.tab ?? currentTab;
          let url = `/api/v2/anthology?view=studio&view2=${tab}&name=${studioName}`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : currentPageSize);
          url += `&limit=${params.pageSize ?? currentPageSize}&offset=${offset}`;
          url += params.keyword ? "&search=" + params.keyword : "";

          url += getSorterUrl(sorter);

          const res = await get<IAnthologyListResponse>(url);
          const items: IItem[] = res.data.rows.map((item, id) => {
            return {
              sn: id + offset + 1,
              id: item.uid,
              title: item.title,
              subtitle: item.subtitle,
              publicity: item.status,
              articles: item.childrenNumber,
              studio: item.studio,
              updated_at: item.updated_at,
            };
          });
          console.log(items);
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
          // F5 重入时从 URL 读到的 currentPage 作为初始值正确生效
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
          showCreate ? (
            <Popover
              content={
                <AnthologyCreate
                  studio={studioName}
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
            </Popover>
          ) : undefined,
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
    </>
  );
};

export default AnthologyListWidget;
