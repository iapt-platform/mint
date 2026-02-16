import { useIntl } from "react-intl";
import { Button, Dropdown, Modal, message, Tag, Space, Tooltip } from "antd";
import { ActionType, ProTable } from "@ant-design/pro-components";
import {
  PlusOutlined,
  DeleteOutlined,
  ExclamationCircleOutlined,
  ImportOutlined,
  ExportOutlined,
  TranslationOutlined,
} from "@ant-design/icons";

import { API_HOST, delete_, get } from "../../../request";
import { IDeleteResponse } from "../../../components/api/Article";
import { get as getUiLang } from "../../../locales";

import { useRef } from "react";

import { IUser } from "../../../reducers/current-user";
import RelationEdit from "../../../components/admin/relation/RelationEdit";
import DataImport from "../../../components/admin/relation/DataImport";
import TimeShow from "../../../components/general/TimeShow";
import { ITerm } from "../../../components/term/TermEdit";
import TermModal from "../../../components/term/TermModal";
import { getSorterUrl } from "../../../utils";

export interface IRelationRequest {
  id?: string;
  name: string;
  name_channel?: string;
  name_term?: ITerm;
  case?: string | null;
  from?: IFrom | null;
  to?: IFrom | null;
  match?: string[];
  category?: string;
  category_channel?: string;
  category_term?: ITerm;
  editor?: IUser;
  updated_at?: string;
  created_at?: string;
}
export interface IRelationListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IRelationRequest[];
    count: number;
  };
}
export interface IRelationResponse {
  ok: boolean;
  message: string;
  data: IRelationRequest;
}
export interface IFrom {
  spell?: string;
  case?: string[];
}
export interface IRelation {
  sn?: number;
  id?: string;
  name: string;
  name_channel?: string;
  name_term?: ITerm;
  case?: string | null;
  from?: IFrom | null;
  fromCase?: string[];
  fromSpell?: string;
  to?: IFrom | null;
  toCase?: string[];
  toSpell?: string;
  match?: string[];
  category?: string;
  category_channel?: string;
  category_term?: ITerm;
  editor?: IUser;
  updated_at?: string;
  created_at?: string;
}
interface IParams {
  name?: string;
  from?: string;
  to?: string;
  match?: string;
  category?: string;
}
const Widget = () => {
  const intl = useIntl(); //i18n

  const showDeleteConfirm = (id?: string, title?: string) => {
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
        return delete_<IDeleteResponse>(`/v2/relation/${id}`)
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
      <ProTable<IRelation, IParams>
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
              id: "forms.fields.relation.label",
            }),
            dataIndex: "name",
            key: "name",
            sorter: true,
            render: (text, row, index, action) => {
              return (
                <RelationEdit
                  id={row.id}
                  trigger={
                    <Button type="link" size="small">
                      {row.name}
                    </Button>
                  }
                  onSuccess={() => {
                    ref.current?.reload();
                  }}
                />
              );
            },
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.name.label",
            }),
            dataIndex: "name1",
            key: "name1",
            search: false,
            render: (text, row, index, action) => {
              return row.name_term ? (
                <TermModal
                  trigger={
                    <Button type="link" icon={<TranslationOutlined />}>
                      {row.name_term?.meaning}
                    </Button>
                  }
                  id={row.name_term.id}
                  channelId={row.category_channel}
                  onUpdate={() => ref.current?.reload()}
                />
              ) : row.name_channel && row.name ? (
                <TermModal
                  trigger={
                    <Button type="link" key="button" icon={<PlusOutlined />}>
                      {intl.formatMessage({ id: "buttons.create" })}
                    </Button>
                  }
                  word={row.name}
                  channelId={row.name_channel}
                  onUpdate={() => ref.current?.reload()}
                />
              ) : undefined;
            },
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.from.label",
            }),
            dataIndex: "from",
            key: "from",
            render: (text, row, index, action) => {
              const spell = row.from?.spell;
              const fromCase = row.from?.case?.map((item, id) => (
                <Tooltip title={item} key={id}>
                  <Tag key={id}>
                    {intl.formatMessage({
                      id: `dict.fields.type.${item}.label`,
                      defaultMessage: item,
                    })}
                  </Tag>
                </Tooltip>
              ));
              return (
                <>
                  {spell}
                  {fromCase}
                </>
              );
            },
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.to.label",
            }),
            dataIndex: "to",
            key: "to",
            render: (text, row, index, action) => {
              const spell = row.to?.spell;
              const toCase = row.to?.case?.map((item, id) => (
                <Tooltip title={item} key={id}>
                  <Tag key={id}>
                    {intl.formatMessage({
                      id: `dict.fields.type.${item}.label`,
                      defaultMessage: item,
                    })}
                  </Tag>
                </Tooltip>
              ));
              return (
                <>
                  {spell}
                  {toCase}
                </>
              );
            },
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.match.label",
            }),
            dataIndex: "match",
            key: "match",
            render: (text, row, index, action) => {
              return row.match?.map((item, id) => <Tag key={id}>{item}</Tag>);
            },
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.category.label",
            }),
            dataIndex: "category",
            key: "category",
            sorter: true,
            render: (text, row, index, action) => {
              return (
                <Space>
                  {row.category}
                  {row.category_term ? (
                    <TermModal
                      trigger={
                        <Button type="link" icon={<TranslationOutlined />}>
                          {row.category_term?.meaning}
                        </Button>
                      }
                      id={row.category_term.id}
                      channelId={row.category_channel}
                      onUpdate={() => ref.current?.reload()}
                    />
                  ) : row.category_channel && row.category ? (
                    <TermModal
                      trigger={
                        <Button
                          type="link"
                          key="button"
                          icon={<PlusOutlined />}
                        >
                          {intl.formatMessage({ id: "buttons.create" })}
                        </Button>
                      }
                      word={row.category}
                      channelId={row.category_channel}
                      onUpdate={() => ref.current?.reload()}
                    />
                  ) : undefined}
                </Space>
              );
            },
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.editor.label",
            }),
            dataIndex: "editor",
            key: "editor",
            render: (text, row, index, action) => {
              return row.editor?.nickName;
            },
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
            render: (text, row, index, action) => {
              return (
                <TimeShow
                  updatedAt={row.updated_at}
                  showLabel={false}
                  showIcon={false}
                />
              );
            },
          },
          {
            title: intl.formatMessage({ id: "buttons.option" }),
            key: "option",
            width: 120,
            valueType: "option",
            render: (text, row, index, action) => {
              return [
                <Dropdown.Button
                  key={index}
                  type="link"
                  menu={{
                    items: [
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
                          break;
                        case "remove":
                          if (row.id) {
                            showDeleteConfirm(row.id, row.name);
                          }

                          break;
                        default:
                          break;
                      }
                    },
                  }}
                >
                  <></>
                </Dropdown.Button>,
              ];
            },
          },
        ]}
        request={async (params = {}, sorter, filter) => {
          console.log("request", params, sorter, filter);
          let url = `/v2/relation?view=a`;
          url += "&ui-lang=" + getUiLang();
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;
          if (typeof params.keyword !== "undefined") {
            url += "&search=" + (params.keyword ? params.keyword : "");
          }
          if (filter.case) {
            url += `&case=${filter.case.join()}`;
          }

          url += params.name ? `&name=${params.name}` : "";
          url += params.match ? `&match=${params.match}` : "";
          url += params.from ? `&from=${params.from}` : "";
          url += params.to ? `&to=${params.to}` : "";
          url += params.category ? `&category=${params.category}` : "";
          url += getSorterUrl(sorter);

          console.log("url", url);
          const res = await get<IRelationListResponse>(url);
          console.log("data", res.data);
          const items: IRelation[] = res.data.rows.map((item, id) => {
            return {
              sn: offset + id + 1,
              id: item.id,
              name: item.name,
              name_channel: item.name_channel,
              name_term: item.name_term,
              case: item.case,
              from: item.from,
              to: item.to,
              match: item.match,
              category: item.category,
              category_channel: item.category_channel,
              category_term: item.category_term,
              editor: item.editor,
              created_at: item.created_at,
              updated_at: item.updated_at,
            };
          });
          console.log("relation", items);
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
        }}
        options={{
          search: true,
        }}
        search={{
          filterType: "light",
        }}
        toolBarRender={() => [
          <DataImport
            url="/v2/relation-import"
            trigger={<Button icon={<ImportOutlined />}>Import</Button>}
            onSuccess={() => {
              ref.current?.reload();
            }}
          />,
          <Button icon={<ExportOutlined />}>
            <a
              href={`${API_HOST}/api/v2/relation-export`}
              target="_blank"
              key="export"
              rel="noreferrer"
            >
              Export
            </a>
          </Button>,
          <RelationEdit
            trigger={
              <Button key="button" icon={<PlusOutlined />} type="primary">
                {intl.formatMessage({ id: "buttons.create" })}
              </Button>
            }
            onSuccess={() => {
              ref.current?.reload();
            }}
          />,
        ]}
      />
    </>
  );
};

export default Widget;
