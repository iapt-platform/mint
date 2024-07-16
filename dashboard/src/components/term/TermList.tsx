import { ActionType, ProTable } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { Button, Space, Table, Dropdown, Modal, message } from "antd";
import {
  ExclamationCircleOutlined,
  DeleteOutlined,
  ImportOutlined,
  PlusOutlined,
} from "@ant-design/icons";

import {
  ITermDeleteRequest,
  ITermListResponse,
} from "../../components/api/Term";
import { delete_2, get } from "../../request";
import { IDeleteResponse } from "../../components/api/Article";
import { useRef } from "react";
import { IChannel } from "../channel/Channel";
import TermExport from "./TermExport";
import DataImport from "../admin/relation/DataImport";
import TermModal from "./TermModal";
import { getSorterUrl } from "../../utils";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";

interface IItem {
  sn: number;
  id: string;
  word: string;
  tag: string;
  channel?: IChannel;
  meaning: string;
  meaning2: string;
  note: string | null;
  updated_at: string;
}

interface IWidget {
  studioName?: string;
  channelId?: string;
}
const TermListWidget = ({ studioName, channelId }: IWidget) => {
  const intl = useIntl();
  const currUser = useAppSelector(currentUser);

  const showDeleteConfirm = (id: string[], title: string) => {
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
        return delete_2<ITermDeleteRequest, IDeleteResponse>(
          `/v2/terms/${id}`,
          {
            uuid: true,
            id: id,
          }
        )
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
      <ProTable<IItem>
        actionRef={ref}
        columns={[
          {
            title: intl.formatMessage({
              id: "term.fields.sn.label",
            }),
            dataIndex: "sn",
            key: "sn",
            width: 80,
            search: false,
          },
          {
            title: intl.formatMessage({
              id: "term.fields.word.label",
            }),
            dataIndex: "word",
            key: "word",
            tooltip: "单词过长会自动收缩",
            ellipsis: true,
          },
          {
            title: intl.formatMessage({
              id: "term.fields.description.label",
            }),
            dataIndex: "tag",
            key: "tag",
            search: false,
          },
          {
            title: intl.formatMessage({
              id: "term.fields.channel.label",
            }),
            dataIndex: "channel",
            key: "channel",
            render(dom, entity, index, action, schema) {
              return entity.channel?.name;
            },
          },
          {
            title: intl.formatMessage({
              id: "term.fields.meaning.label",
            }),
            dataIndex: "meaning",
            key: "meaning",
          },
          {
            title: intl.formatMessage({
              id: "term.fields.meaning2.label",
            }),
            dataIndex: "meaning2",
            key: "meaning2",
            tooltip: "意思过长会自动收缩",
            ellipsis: true,
          },
          {
            title: intl.formatMessage({
              id: "term.fields.note.label",
            }),
            dataIndex: "note",
            key: "note",
            search: false,
            tooltip: "注释过长会自动收缩",
            ellipsis: true,
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.updated-at.label",
            }),
            key: "updated_at",
            width: 200,
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
                        case "remove":
                          showDeleteConfirm([row.id], row.word);
                          break;
                        default:
                          break;
                      }
                    },
                  }}
                >
                  <TermModal
                    trigger={intl.formatMessage({
                      id: "buttons.edit",
                    })}
                    id={row.id}
                    studioName={studioName}
                    channelId={channelId}
                    onUpdate={() => ref.current?.reload()}
                  />
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
              <Button
                type="link"
                style={{ marginInlineStart: 8 }}
                onClick={onCleanSelected}
              >
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
            <Space size={16}>
              <Button
                type="link"
                onClick={() => {
                  console.log(selectedRowKeys);
                  showDeleteConfirm(
                    selectedRowKeys.map((item) => item.toString()),
                    selectedRowKeys.length + "个单词"
                  );
                  onCleanSelected();
                }}
              >
                批量删除
              </Button>
            </Space>
          );
        }}
        request={async (params = {}, sorter, filter) => {
          console.log(params, sorter, filter);
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          let url = `/v2/terms?`;
          if (typeof channelId === "string") {
            url += `view=channel&id=${channelId}`;
          } else {
            url += `view=studio&name=${studioName}`;
          }

          url += `&limit=${params.pageSize}&offset=${offset}`;
          if (typeof params.keyword !== "undefined") {
            url += "&search=" + (params.keyword ? params.keyword : "");
          }
          url += getSorterUrl(sorter);
          const res = await get<ITermListResponse>(url);
          console.log(res);
          const items: IItem[] = res.data.rows.map((item, id) => {
            return {
              sn: id + offset + 1,
              id: item.guid,
              word: item.word,
              tag: item.tag,
              channel: item.channel,
              meaning: item.meaning,
              meaning2: item.other_meaning,
              note: item.note,
              updated_at: item.updated_at,
            };
          });
          return {
            total: res.data.count,
            success: true,
            data: items,
          };
        }}
        rowKey="id"
        //bordered
        pagination={{
          showQuickJumper: true,
          showSizeChanger: true,
        }}
        toolBarRender={() => [
          <DataImport
            url="/v2/terms-import"
            urlExtra={
              channelId
                ? `view=channel&id=${channelId}`
                : `view=studio&name=${studioName}`
            }
            trigger={
              <Button icon={<ImportOutlined />}>
                {intl.formatMessage({ id: "buttons.import" })}
              </Button>
            }
            onSuccess={() => {
              ref.current?.reload();
            }}
          />,
          <TermExport channelId={channelId} studioName={studioName} />,
          <TermModal
            trigger={
              <Button
                key="button"
                icon={<PlusOutlined />}
                type="primary"
                disabled={currUser?.roles?.includes("basic")}
              >
                {intl.formatMessage({ id: "buttons.create" })}
              </Button>
            }
            studioName={studioName}
            channelId={channelId}
            onUpdate={() => ref.current?.reload()}
          />,
        ]}
        search={false}
        options={{
          search: true,
        }}
        dateFormatter="string"
      />
    </>
  );
};

export default TermListWidget;
