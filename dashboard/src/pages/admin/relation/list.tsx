import { useIntl } from "react-intl";
import { Button, Dropdown, Modal, message, Tag } from "antd";
import { ActionType, ProTable } from "@ant-design/pro-components";
import {
  PlusOutlined,
  DeleteOutlined,
  ExclamationCircleOutlined,
  ImportOutlined,
  ExportOutlined,
} from "@ant-design/icons";

import { API_HOST, delete_, get } from "../../../request";
import { IDeleteResponse } from "../../../components/api/Article";

import { useRef } from "react";

import { IUser } from "../../../reducers/current-user";
import RelationEdit from "../../../components/admin/relation/RelationEdit";
import DataImport from "../../../components/admin/relation/DataImport";
import { useAppSelector } from "../../../hooks";
import { getTerm } from "../../../reducers/term-vocabulary";

export const CaseValueEnum = () => {
  const intl = useIntl();
  return {
    all: {
      text: "all",
    },
    nom: {
      text: intl.formatMessage({
        id: "dict.fields.type.nom.label",
      }),
    },
    acc: {
      text: intl.formatMessage({
        id: "dict.fields.type.acc.label",
      }),
    },
    gen: {
      text: intl.formatMessage({
        id: "dict.fields.type.gen.label",
      }),
    },
    dat: {
      text: intl.formatMessage({
        id: "dict.fields.type.dat.label",
      }),
    },
    inst: {
      text: intl.formatMessage({
        id: "dict.fields.type.inst.label",
      }),
    },
    abl: {
      text: intl.formatMessage({
        id: "dict.fields.type.abl.label",
      }),
    },
    loc: {
      text: intl.formatMessage({
        id: "dict.fields.type.loc.label",
      }),
    },
  };
};

export interface IRelationRequest {
  id?: string;
  name: string;
  case?: string | null;
  to?: string[];
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
export interface IRelation {
  sn?: number;
  id?: string;
  name: string;
  case?: string | null;
  to?: string[];
  updatedAt?: number;
  createdAt?: number;
}
const Widget = () => {
  const intl = useIntl(); //i18n
  const terms = useAppSelector(getTerm);

  const showDeleteConfirm = (id?: string, title?: string) => {
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
      <ProTable<IRelation>
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
            dataIndex: "relation",
            key: "relation",
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
            render: (text, row, index, action) => {
              const localName = terms?.find(
                (item) => item.word === row.name
              )?.meaning;
              return localName;
            },
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.case.label",
            }),
            dataIndex: "case",
            key: "case",
            filters: true,
            valueEnum: CaseValueEnum(),
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.to.label",
            }),
            dataIndex: "to",
            key: "to",
            render: (text, row, index, action) => {
              return row.to?.map((item, id) => (
                <Tag key={id}>
                  {intl.formatMessage({
                    id: `dict.fields.type.${item}.label`,
                  })}
                </Tag>
              ));
            },
          },

          {
            title: intl.formatMessage({
              id: "forms.fields.updated-at.label",
            }),
            key: "updated-at",
            width: 100,
            search: false,
            dataIndex: "updatedAt",
            valueType: "date",
            sorter: (a, b) =>
              a.updatedAt && b.updatedAt ? a.updatedAt - b.updatedAt : 0,
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
          console.log("url", url);
          const res = await get<IRelationListResponse>(url);
          const items: IRelation[] = res.data.rows.map((item, id) => {
            const date = new Date(item.created_at ? item.created_at : 0);
            const date2 = new Date(item.updated_at ? item.updated_at : 0);
            return {
              sn: offset + id + 1,
              id: item.id,
              name: item.name,
              case: item.case,
              to: item.to,
              createdAt: date.getTime(),
              updatedAt: date2.getTime(),
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
        search={false}
        options={{
          search: true,
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
