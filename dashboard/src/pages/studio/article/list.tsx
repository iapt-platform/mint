import { useParams, Link } from "react-router-dom";
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

import ArticleCreate from "../../../components/article/ArticleCreate";
import { delete_, get } from "../../../request";
import {
  IArticleListResponse,
  IDeleteResponse,
} from "../../../components/api/Article";
import { PublicityValueEnum } from "../../../components/studio/table";
import { useRef, useState } from "react";
import ArticleTplMaker from "../../../components/article/ArticleTplMaker";
import ShareModal from "../../../components/share/ShareModal";
import { EResType } from "../../../components/share/Share";
import AddToAnthology from "../../../components/article/AddToAnthology";
import AnthologySelect from "../../../components/anthology/AnthologySelect";

const { Text } = Typography;

interface DataItem {
  sn: number;
  id: string;
  title: string;
  subtitle: string;
  summary: string;
  anthologyCount?: number;
  anthologyTitle?: string;
  publicity: number;
  createdAt: number;
}
const Widget = () => {
  const intl = useIntl(); //i18n
  const { studioname } = useParams(); //url 参数
  const [openCreate, setOpenCreate] = useState(false);
  const [anthologyId, setAnthologyId] = useState<string>();

  const showDeleteConfirm = (id: string, title: string) => {
    Modal.confirm({
      icon: <ExclamationCircleOutlined />,
      title:
        intl.formatMessage({
          id: "message.delete.sure",
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
            tip: "过长会自动收缩",
            ellipsis: true,
            render: (text, row, index, action) => {
              return (
                <>
                  <div>
                    <Link to={`/studio/${studioname}/article/${row.id}/edit`}>
                      {row.title}
                    </Link>
                  </div>
                  <Text type="secondary">{row.subtitle}</Text>
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
            tip: "过长会自动收缩",
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
              id: "forms.fields.created-at.label",
            }),
            key: "created-at",
            width: 100,
            search: false,
            dataIndex: "createdAt",
            valueType: "date",
            sorter: (a, b) => a.createdAt - b.createdAt,
          },
          {
            title: intl.formatMessage({ id: "buttons.option" }),
            key: "option",
            width: 120,
            valueType: "option",
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
                          <ArticleTplMaker
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
                        label: (
                          <ShareModal
                            trigger={intl.formatMessage({
                              id: "buttons.share",
                            })}
                            resId={row.id}
                            resType={EResType.article}
                          />
                        ),
                        icon: <TeamOutlined />,
                      },
                      {
                        key: "addToAnthology",
                        label: (
                          <AddToAnthology
                            trigger="加入文集"
                            studioName={studioname}
                            articleIds={[row.id]}
                          />
                        ),
                        icon: <FolderAddOutlined />,
                      },
                      {
                        key: "remove",
                        label: (
                          <Text type="danger">
                            {intl.formatMessage({
                              id: "buttons.delete",
                            })}
                          </Text>
                        ),
                        icon: (
                          <Text type="danger">
                            <DeleteOutlined />
                          </Text>
                        ),
                      },
                    ],
                    onClick: (e) => {
                      switch (e.key) {
                        case "share":
                          break;
                        case "remove":
                          showDeleteConfirm(row.id, row.title);
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
        rowSelection={{
          // 自定义选择项参考: https://ant.design/components/table-cn/#components-table-demo-row-selection-custom
          // 注释该行则默认不显示下拉选项
          selections: [Table.SELECTION_ALL, Table.SELECTION_INVERT],
        }}
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
            <AddToAnthology
              studioName={studioname}
              articleIds={selectedRowKeys.map((item) => item.toString())}
              onFinally={() => {
                onCleanSelected();
              }}
            />
          );
        }}
        request={async (params = {}, sorter, filter) => {
          console.log(params, sorter, filter);
          console.log("anthology", anthologyId);
          let url = `/v2/article?view=studio&name=${studioname}`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;
          if (typeof params.keyword !== "undefined") {
            url += "&search=" + (params.keyword ? params.keyword : "");
          }
          if (typeof anthologyId !== "undefined") {
            url += "&anthology=" + anthologyId;
          }
          const res = await get<IArticleListResponse>(url);
          console.log("article list", res);
          const items: DataItem[] = res.data.rows.map((item, id) => {
            const date = new Date(item.created_at);
            return {
              sn: id + 1,
              id: item.uid,
              title: item.title,
              subtitle: item.subtitle,
              summary: item.summary,
              anthologyCount: item.anthology_count,
              anthologyTitle: item.anthology_first?.title,
              publicity: item.status,
              createdAt: date.getTime(),
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
          pageSize: 10,
        }}
        search={false}
        options={{
          search: true,
        }}
        toolBarRender={() => [
          <AnthologySelect
            studioName={studioname}
            onSelect={(value: string) => {
              setAnthologyId(value);
              ref.current?.reload();
            }}
          />,
          <Popover
            content={
              <ArticleCreate
                studio={studioname}
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
      />
    </>
  );
};

export default Widget;
