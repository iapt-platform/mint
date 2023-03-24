import { useIntl } from "react-intl";
import { Button, Dropdown, Typography, Modal, message } from "antd";
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
import NissayaEndingEdit from "../../../components/admin/relation/NissayaEndingEdit";
import { LangValueEnum } from "../../../components/general/LangSelect";
import { NissayaCardModal } from "../../../components/general/NissayaCard";
import DataImport from "../../../components/admin/relation/DataImport";
import { CaseValueEnum } from "../relation/list";

const { Text } = Typography;
export interface INissayaEndingRequest {
  id?: string;
  ending?: string;
  lang?: string;
  relation?: string;
  case?: string;
  editor?: IUser;
  count?: number;
  updated_at?: string;
  created_at?: string;
}
export interface INissayaEndingListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: INissayaEndingRequest[];
    count: number;
  };
}
export interface INissayaEndingResponse {
  ok: boolean;
  message: string;
  data: INissayaEndingRequest;
}
export interface INissayaEnding {
  sn?: number;
  id?: string;
  ending?: string;
  lang?: string;
  relation?: string;
  case?: string;
  count?: number;
  updatedAt?: number;
  createdAt?: number;
}
const Widget = () => {
  const intl = useIntl(); //i18n

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
        return delete_<IDeleteResponse>(`/v2/nissaya-ending/${id}`)
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
      <ProTable<INissayaEnding>
        actionRef={ref}
        search={{
          filterType: "light",
        }}
        options={{
          search: true,
        }}
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
              id: "forms.fields.ending.label",
            }),
            dataIndex: "ending",
            key: "ending",
            search: false,
            render: (text, row, index, action) => {
              return (
                <NissayaEndingEdit
                  id={row.id}
                  trigger={
                    <Button type="link" size="small">
                      {row.ending}
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
              id: "forms.fields.lang.label",
            }),
            dataIndex: "lang",
            key: "lang",
            search: false,
            filters: true,
            valueEnum: LangValueEnum(),
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.relation.label",
            }),
            dataIndex: "relation",
            key: "relation",
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.case.label",
            }),
            dataIndex: "case",
            key: "case",
            search: false,
            filters: true,
            valueEnum: CaseValueEnum(),
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.count.label",
            }),
            key: "count",
            width: 100,
            search: false,
            dataIndex: "count",
            sorter: (a, b) => (a.count && b.count ? a.count - b.count : 0),
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
            sorter: (a, b) =>
              a.createdAt && b.createdAt ? a.createdAt - b.createdAt : 0,
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
                          if (row.id) {
                            showDeleteConfirm(row.id, row.ending);
                          }

                          break;
                        default:
                          break;
                      }
                    },
                  }}
                >
                  <NissayaCardModal
                    key={index}
                    text={row.ending}
                    trigger={
                      <>
                        {intl.formatMessage({
                          id: "buttons.preview",
                        })}
                      </>
                    }
                  />
                </Dropdown.Button>,
              ];
            },
          },
        ]}
        request={async (params = {}, sorter, filter) => {
          console.log(params, sorter, filter);
          let url = `/v2/nissaya-ending?view=a`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;
          if (typeof params.keyword !== "undefined") {
            url += "&search=" + (params.keyword ? params.keyword : "");
          }
          if (params.relation) {
            url += `&relation=${params.relation}`;
          }
          if (filter.case) {
            url += `&case=${filter.case.join()}`;
          }
          if (filter.lang) {
            url += `&lang=${filter.lang.join()}`;
          }
          const res = await get<INissayaEndingListResponse>(url);
          const items: INissayaEnding[] = res.data.rows.map((item, id) => {
            const date = new Date(item.created_at ? item.created_at : 0);
            const date2 = new Date(item.updated_at ? item.updated_at : 0);
            return {
              sn: id + 1,
              id: item.id,
              ending: item.ending,
              lang: item.lang,
              relation: item.relation,
              case: item.case,
              count: item.count,
              createdAt: date.getTime(),
              updatedAt: date2.getTime(),
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
        }}
        toolBarRender={() => [
          <DataImport
            url="/v2/nissaya-ending-import"
            trigger={<Button icon={<ImportOutlined />}>Import</Button>}
            onSuccess={() => {
              ref.current?.reload();
            }}
          />,
          <Button icon={<ExportOutlined />}>
            <a
              href={`${API_HOST}/api/v2/nissaya-ending-export`}
              target="_blank"
              key="export"
              rel="noreferrer"
            >
              Export
            </a>
          </Button>,
          <NissayaEndingEdit
            key="add"
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
