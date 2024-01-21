import { useIntl } from "react-intl";
import { Button, Dropdown, Modal, message, Space, Tag } from "antd";
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

import { get as getUiLang } from "../../../locales";
import { IUser } from "../../../reducers/current-user";
import NissayaEndingEdit from "../../../components/admin/relation/NissayaEndingEdit";
import { LangValueEnum } from "../../../components/general/LangSelect";
import { NissayaCardModal } from "../../../components/general/NissayaCard";
import DataImport from "../../../components/admin/relation/DataImport";
import TermModal from "../../../components/term/TermModal";
import { ITermDataResponse } from "../../../components/api/Term";
import TimeShow from "../../../components/general/TimeShow";
import { IFrom } from "../relation/list";
import { getSorterUrl } from "../../../utils";

export interface INissayaEndingRequest {
  id?: string;
  ending?: string;
  lang?: string;
  relation?: string;
  case?: string;
  from?: IFrom | null;
  editor?: IUser;
  count?: number;
  term_id?: string;
  term_channel?: string;
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
  from?: IFrom | null;
  fromCase?: string[];
  fromSpell?: string;
  count?: number;
  termId?: string;
  termChannel?: string;
  updated_at?: string;
  createdAt?: number;
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
            sorter: true,
            render: (text, row, index, action) => {
              return (
                <Space>
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
                  {row.termChannel ? (
                    <TermModal
                      onUpdate={(value: ITermDataResponse) => {
                        //onModalClose();
                      }}
                      onClose={() => {
                        //onModalClose();
                      }}
                      trigger={<Button size="small">术语维护</Button>}
                      id={row.termId}
                      word={row.ending}
                      channelId={row.termChannel}
                    />
                  ) : undefined}
                </Space>
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
              id: "forms.fields.from.label",
            }),
            dataIndex: "from",
            key: "from",
            render: (text, row, index, action) => {
              const spell = row.from?.spell;
              const fromCase = row.from?.case?.map((item, id) => (
                <Tag key={id}>
                  {intl.formatMessage({
                    id: `dict.fields.type.${item}.label`,
                    defaultMessage: item,
                  })}
                </Tag>
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
              id: "forms.fields.relation.label",
            }),
            dataIndex: "relation",
            key: "relation",
            sorter: true,
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.count.label",
            }),
            key: "count",
            width: 100,
            search: false,
            dataIndex: "count",
            sorter: true,
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
                  showLabel
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
          url += "&ui-lang=" + getUiLang();
          if (params.keyword) {
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
          url += getSorterUrl(sorter);

          console.log("url", url);
          const res = await get<INissayaEndingListResponse>(url);
          const items: INissayaEnding[] = res.data.rows.map((item, id) => {
            return {
              sn: offset + id + 1,
              id: item.id,
              ending: item.ending,
              lang: item.lang,
              relation: item.relation,
              case: item.case,
              from: item.from,
              count: item.count,
              termId: item.term_id,
              termChannel: item.term_channel,
              updated_at: item.updated_at,
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
