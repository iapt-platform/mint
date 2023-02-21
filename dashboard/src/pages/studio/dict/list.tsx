import { useParams } from "react-router-dom";
import { useIntl } from "react-intl";

import {
  Button,
  Space,
  Table,
  Dropdown,
  Drawer,
  message,
  Modal,
  Typography,
} from "antd";
import {
  PlusOutlined,
  ExclamationCircleOutlined,
  DeleteOutlined,
} from "@ant-design/icons";
import { ActionType, ProTable } from "@ant-design/pro-components";

import DictCreate from "../../../components/dict/DictCreate";
import { IApiResponseDictList } from "../../../components/api/Dict";
import { delete_, get } from "../../../request";
import { useRef, useState } from "react";
import DictEdit from "../../../components/dict/DictEdit";
import { IDeleteResponse } from "../../../components/api/Article";

const { Text } = Typography;

interface IItem {
  sn: number;
  wordId: string;
  word: string;
  type: string;
  grammar: string;
  parent: string;
  meaning: string;
  note: string;
  factors: string;
  createdAt: number;
}

const Widget = () => {
  const intl = useIntl();
  const { studioname } = useParams();
  const [isEditOpen, setIsEditOpen] = useState(false);
  const [isCreateOpen, setIsCreateOpen] = useState(false);
  const [wordId, setWordId] = useState<string>();
  const [drawerTitle, setDrawerTitle] = useState("New Word");

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
        return delete_<IDeleteResponse>(`/v2/userdict/${id}`)
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
              id: "dict.fields.sn.label",
            }),
            dataIndex: "sn",
            key: "sn",
            width: 80,
            search: false,
          },
          {
            title: intl.formatMessage({
              id: "dict.fields.word.label",
            }),
            dataIndex: "word",
            key: "word",
            tip: "单词过长会自动收缩",
            ellipsis: true,
          },
          {
            title: intl.formatMessage({
              id: "dict.fields.type.label",
            }),
            dataIndex: "type",
            key: "type",
            search: false,
            filters: true,
            onFilter: true,
            valueEnum: {
              all: { text: "全部", status: "Default" },
              n: { text: "名词", status: "Default" },
              ti: { text: "三性", status: "Processing" },
              v: { text: "动词", status: "Success" },
              ind: { text: "不变词", status: "Success" },
            },
          },
          {
            title: intl.formatMessage({
              id: "dict.fields.grammar.label",
            }),
            dataIndex: "grammar",
            key: "grammar",
            search: false,
          },
          {
            title: intl.formatMessage({
              id: "dict.fields.parent.label",
            }),
            dataIndex: "parent",
            key: "parent",
          },
          {
            title: intl.formatMessage({
              id: "dict.fields.meaning.label",
            }),
            dataIndex: "meaning",
            key: "meaning",
            tip: "意思过长会自动收缩",
            ellipsis: true,
          },
          {
            title: intl.formatMessage({
              id: "dict.fields.note.label",
            }),
            dataIndex: "note",
            key: "note",
            search: false,
            tip: "注释过长会自动收缩",
            ellipsis: true,
          },
          {
            title: intl.formatMessage({
              id: "dict.fields.factors.label",
            }),
            dataIndex: "factors",
            key: "factors",
            search: false,
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
                        case "share":
                          break;
                        case "remove":
                          showDeleteConfirm(row.wordId, row.word);
                          break;
                        default:
                          break;
                      }
                    },
                  }}
                >
                  <Button
                    type="link"
                    size="small"
                    onClick={() => {
                      setWordId(row.wordId);
                      setDrawerTitle(row.word);
                      setIsEditOpen(true);
                    }}
                  >
                    {intl.formatMessage({
                      id: "buttons.edit",
                    })}
                  </Button>
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
        tableAlertOptionRender={() => {
          return (
            <Space size={16}>
              <Button type="link">批量删除</Button>
              <Button type="link">导出数据</Button>
            </Space>
          );
        }}
        request={async (params = {}, sorter, filter) => {
          // TODO
          console.log(params, sorter, filter);
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          let url = `/v2/userdict?view=studio&name=${studioname}&limit=${params.pageSize}&offset=${offset}`;
          if (typeof params.keyword !== "undefined") {
            url += "&search=" + (params.keyword ? params.keyword : "");
          }
          console.log(url);
          const res = await get<IApiResponseDictList>(url);

          const items: IItem[] = res.data.rows.map((item, id) => {
            const date = new Date(item.updated_at);
            const id2 =
              ((params.current || 1) - 1) * (params.pageSize || 20) + id + 1;
            return {
              sn: id2,
              wordId: item.id,
              word: item.word,
              type: item.type,
              grammar: item.grammar,
              parent: item.parent,
              meaning: item.mean,
              note: item.note,
              factors: item.factors,
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
        bordered
        pagination={{
          showQuickJumper: true,
          showSizeChanger: true,
        }}
        search={false}
        options={{
          search: true,
        }}
        headerTitle=""
        toolBarRender={() => [
          <Button
            key="button"
            icon={<PlusOutlined />}
            type="primary"
            onClick={() => {
              setDrawerTitle("New word");
              setIsCreateOpen(true);
            }}
          >
            {intl.formatMessage({ id: "buttons.create" })}
          </Button>,
        ]}
      />

      <Drawer
        title={drawerTitle}
        placement="right"
        open={isCreateOpen}
        onClose={() => {
          setIsCreateOpen(false);
        }}
        key="create"
        style={{ maxWidth: "100%" }}
        contentWrapperStyle={{ overflowY: "auto" }}
        footer={null}
      >
        <DictCreate studio={studioname ? studioname : ""} />
      </Drawer>
      <Drawer
        title={drawerTitle}
        placement="right"
        open={isEditOpen}
        onClose={() => {
          setIsEditOpen(false);
        }}
        key="edit"
        style={{ maxWidth: "100%" }}
        contentWrapperStyle={{ overflowY: "auto" }}
        footer={null}
      >
        <DictEdit wordId={wordId} />
      </Drawer>
    </>
  );
};

export default Widget;
