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

import DictCreate from "./DictCreate";
import {
  IApiResponseDictList,
  IDictInfo,
  IUserDictDeleteRequest,
} from "../api/Dict";
import { delete_2, get } from "../../request";
import { useRef, useState } from "react";
import DictEdit from "./DictEdit";
import { IDeleteResponse } from "../api/Article";
import TimeShow from "../general/TimeShow";
import { getSorterUrl } from "../../utils";

const { Link } = Typography;

export interface IWord {
  sn: number;
  wordId: string;
  word: string;
  type?: string | null;
  grammar?: string | null;
  parent?: string | null;
  meaning?: string | null;
  note?: string | null;
  factors?: string | null;
  dict?: IDictInfo;
  updated_at?: string;
  created_at?: string;
}
interface IParams {
  word?: string;
  parent?: string;
  dict?: string;
}
interface IWidget {
  studioName?: string;
  view?: "studio" | "all";
  dictName?: string;
}
const UserDictTableWidget = ({
  studioName,
  view = "studio",
  dictName,
}: IWidget) => {
  const intl = useIntl();
  const [isEditOpen, setIsEditOpen] = useState(false);
  const [isCreateOpen, setIsCreateOpen] = useState(false);
  const [wordId, setWordId] = useState<string>();
  const [drawerTitle, setDrawerTitle] = useState("New Word");

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
        return delete_2<IUserDictDeleteRequest, IDeleteResponse>(
          `/v2/userdict/${id}`,
          {
            id: JSON.stringify(id),
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
      <ProTable<IWord, IParams>
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
            tooltip: "单词过长会自动收缩",
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
            tooltip: "意思过长会自动收缩",
            ellipsis: true,
            search: false,
          },
          {
            title: intl.formatMessage({
              id: "dict.fields.note.label",
            }),
            dataIndex: "note",
            key: "note",
            search: false,
            tooltip: "注释过长会自动收缩",
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
              id: "forms.fields.dict.shortname.label",
            }),
            dataIndex: "dict",
            key: "dict",
            hideInTable: view !== "all",
            search: view !== "all" ? false : undefined,
            render: (text, row, index, action) => {
              return row.dict?.shortname;
            },
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
            render: (text, row, index, action) => {
              return (
                <TimeShow
                  updatedAt={row.updated_at}
                  showIcon={false}
                  showLabel={false}
                />
              );
            },
          },
          {
            title: intl.formatMessage({ id: "buttons.option" }),
            key: "option",
            hideInTable: view === "all",
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
                          showDeleteConfirm([row.wordId], row.word);
                          break;
                        default:
                          break;
                      }
                    },
                  }}
                >
                  <Link
                    onClick={() => {
                      setWordId(row.wordId);
                      setDrawerTitle(row.word);
                      setIsEditOpen(true);
                    }}
                  >
                    {intl.formatMessage({
                      id: "buttons.edit",
                    })}
                  </Link>
                </Dropdown.Button>,
              ];
            },
          },
        ]}
        rowSelection={
          view === "all"
            ? undefined
            : {
                // 自定义选择项参考: https://ant.design/components/table-cn/#components-table-demo-row-selection-custom
                // 注释该行则默认不显示下拉选项
                selections: [Table.SELECTION_ALL, Table.SELECTION_INVERT],
              }
        }
        tableAlertRender={
          view === "all"
            ? undefined
            : ({ selectedRowKeys, selectedRows, onCleanSelected }) => (
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
              )
        }
        tableAlertOptionRender={
          view === "all"
            ? undefined
            : ({ intl, selectedRowKeys, selectedRows, onCleanSelected }) => {
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
              }
        }
        request={async (params = {}, sorter, filter) => {
          console.log(params, sorter, filter);
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);

          let url = "/v2/userdict?";
          switch (view) {
            case "studio":
              url += `view=studio&name=${studioName}`;
              break;
            case "all":
              url += `view=all`;
              break;
            default:
              break;
          }
          url += `&limit=${params.pageSize}&offset=${offset}`;

          url += params.keyword ? "&search=" + params.keyword : "";

          url += params.word ? `&word=${params.word}` : "";
          url += params.parent ? `&parent=${params.parent}` : "";
          url += params.dict ? `&dict=${params.dict}` : "";
          url += dictName
            ? dictName !== "all"
              ? `&dict=${dictName}`
              : ""
            : "";

          url += getSorterUrl(sorter);

          console.log(url);
          const res = await get<IApiResponseDictList>(url);
          const items: IWord[] = res.data.rows.map((item, id) => {
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
              dict: item.dict,
              updated_at: item.updated_at,
            };
          });
          return {
            total: res.data.count,
            success: true,
            data: items,
          };
        }}
        rowKey="wordId"
        bordered
        pagination={{
          showQuickJumper: true,
          showSizeChanger: true,
        }}
        search={{
          filterType: "light",
        }}
        options={{
          search: true,
        }}
        headerTitle=""
        toolBarRender={
          view === "all"
            ? undefined
            : () => [
                <Button
                  key="button"
                  icon={<PlusOutlined />}
                  type="primary"
                  onClick={() => {
                    setDrawerTitle("New word");
                    setIsCreateOpen(true);
                  }}
                  disabled={true}
                >
                  {intl.formatMessage({ id: "buttons.create" })}
                </Button>,
              ]
        }
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
        <DictCreate studio={studioName ? studioName : ""} />
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

export default UserDictTableWidget;
