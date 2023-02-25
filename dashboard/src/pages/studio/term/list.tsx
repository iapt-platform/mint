import { useParams } from "react-router-dom";
import { ActionType, ProTable } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import {
  Button,
  Space,
  Table,
  Dropdown,
  Typography,
  Modal,
  message,
} from "antd";
import { ExclamationCircleOutlined, DeleteOutlined } from "@ant-design/icons";

import {
  ITermDeleteRequest,
  ITermListResponse,
} from "../../../components/api/Term";
import { delete_2, get } from "../../../request";
import TermCreate from "../../../components/term/TermCreate";
import { IDeleteResponse } from "../../../components/api/Article";
import { useRef } from "react";

const { Text } = Typography;

interface IItem {
  sn: number;
  id: string;
  word: string;
  tag: string;
  channel: string;
  meaning: string;
  meaning2: string;
  note: string;
  createdAt: number;
}

const Widget = () => {
  const intl = useIntl();
  const { studioname } = useParams();

  const showDeleteConfirm = (id: string[], title: string) => {
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
            tip: "单词过长会自动收缩",
            ellipsis: true,
            formItemProps: {
              rules: [
                {
                  required: true,
                  message: "此项为必填项",
                },
              ],
            },
          },
          {
            title: intl.formatMessage({
              id: "term.fields.description.label",
            }),
            dataIndex: "tag",
            key: "description",
            search: false,
          },
          {
            title: intl.formatMessage({
              id: "term.fields.channel.label",
            }),
            dataIndex: "channel",
            valueType: "select",
            valueEnum: {
              all: { text: "全部" },
              1: { text: "中文" },
              2: { text: "中文草稿" },
              3: { text: "英文" },
              4: { text: "英文草稿" },
              5: { text: "Visuddhinanda" },
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
            tip: "意思过长会自动收缩",
            ellipsis: true,
          },
          {
            title: intl.formatMessage({
              id: "term.fields.note.label",
            }),
            dataIndex: "note",
            key: "note",
            search: false,
            tip: "注释过长会自动收缩",
            ellipsis: true,
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.created-at.label",
            }),
            key: "created-at",
            width: 200,
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
                        case "remove":
                          showDeleteConfirm([row.id], row.word);
                          break;
                        default:
                          break;
                      }
                    },
                  }}
                >
                  <TermCreate
                    studio={studioname}
                    isCreate={false}
                    wordId={row.id}
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
          // TODO
          console.log(params, sorter, filter);
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          let url = `/v2/terms?view=studio&name=${studioname}&limit=${params.pageSize}&offset=${offset}`;
          if (typeof params.keyword !== "undefined") {
            url += "&search=" + (params.keyword ? params.keyword : "");
          }
          console.log(url);
          const res = await get<ITermListResponse>(url);

          const items: IItem[] = res.data.rows.map((item, id) => {
            const date = new Date(item.updated_at);
            const id2 =
              ((params.current || 1) - 1) * (params.pageSize || 20) + id + 1;
            return {
              sn: id2,
              id: item.guid,
              word: item.word,
              tag: item.tag,
              channel: item.channal,
              meaning: item.meaning,
              meaning2: item.other_meaning,
              note: item.note,
              createdAt: date.getTime(),
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
          <TermCreate isCreate={true} studio={studioname} />,
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

export default Widget;
