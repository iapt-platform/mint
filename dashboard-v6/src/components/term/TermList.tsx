import { type ActionType, ProTable } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { Button, Space, Table, Dropdown, Modal, message } from "antd";
import {
  ExclamationCircleOutlined,
  DeleteOutlined,
  PlusOutlined,
} from "@ant-design/icons";

import {
  deleteTerm,
  fetchChannelTerms,
  fetchStudioTerms,
  type TermExchangeTarget,
  type TermListQuery,
} from "../../api/Term";
import { useRef } from "react";
import TermExport from "./TermExport";
import TermImport from "./TermImport";

import TermModal from "./TermModal";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";
import { Link } from "react-router";
import { useLoadGuard } from "../../hooks/useLoadGuard";
import ApiErrorResult from "../general/ApiErrorResult";

interface IItem {
  sn: number;
  id: string;
  word: string;
  tag: string;
  channel?: string;
  meaning: string;
  meaning2: string;
  note: string | null;
  updated_at: string;
}

interface IWidget {
  /** v3 列表用：用户或团队的 uuid */
  studioId?: string;
  /** TermModal 的「在 Studio 中打开」用 */
  studioName?: string;
  channelId?: string;
}
const TermListWidget = ({ studioId, studioName, channelId }: IWidget) => {
  const load = useLoadGuard();
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
      async onOk() {
        // v3 一条一条软删；没有删除权限的那条会弹出提示并中止
        for (const termId of id) {
          await deleteTerm(termId);
        }
        message.success("删除成功");
        ref.current?.reload();
      },
    });
  };

  const ref = useRef<ActionType | null>(null);
  // 导入 / 导出的范围：channel 详情页按 channel，术语页按当前身份的 studio
  const exchangeTarget: TermExchangeTarget | undefined =
    typeof channelId === "string"
      ? { channelId }
      : studioId
        ? { studioId }
        : undefined;

  // 加载失败（超时、网络不通…）只显示错误页，不显示空列表
  if (load.error) {
    return <ApiErrorResult error={load.error} onRetry={load.retry} />;
  }

  return (
    <>
      <ProTable<IItem>
        onRequestError={load.onError}
        key={load.attempt}
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
            render(_dom, entity) {
              return (
                <Link to={`/workspace/term/${entity.id}`}>{entity.word}</Link>
              );
            },
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
            render(_dom, entity) {
              return (
                entity.channel ??
                intl.formatMessage({ id: "term.general-in-studio" })
              );
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
            render: (_text, row, index) => {
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
        tableAlertRender={({ selectedRowKeys, onCleanSelected }) => (
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
        tableAlertOptionRender={({ selectedRowKeys, onCleanSelected }) => {
          return (
            <Space size={16}>
              <Button
                type="link"
                onClick={() => {
                  console.log(selectedRowKeys);
                  showDeleteConfirm(
                    selectedRowKeys.map((item) => item.toString()),
                    selectedRowKeys.length + "个单词",
                  );
                  onCleanSelected();
                }}
              >
                批量删除
              </Button>
            </Space>
          );
        }}
        request={async (params = {}, sorter) => {
          const page = params.current ?? 1;
          const perPage = params.pageSize ?? 20;
          const query: TermListQuery = {
            page,
            per_page: perPage,
            search: params.keyword || undefined,
          };
          const sort = sorter?.updated_at;
          if (sort) {
            query.order = "updated_at";
            query.dir = sort === "ascend" ? "asc" : "desc";
          }
          const res =
            typeof channelId === "string"
              ? await fetchChannelTerms(channelId, query)
              : studioId
                ? await fetchStudioTerms(studioId, query)
                : undefined;
          const offset = (page - 1) * perPage;
          const items: IItem[] = (res?.data ?? []).map((item, index) => ({
            sn: index + offset + 1,
            id: item.id ?? "",
            word: item.word ?? "",
            tag: item.tag ?? "",
            channel: item.channel?.name,
            meaning: item.meaning ?? "",
            meaning2: item.other_meaning ?? "",
            note: item.summary_text ?? "",
            updated_at: item.updated_at ?? "",
          }));
          return {
            total: res?.meta?.total ?? 0,
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
          <TermImport
            key="import"
            target={exchangeTarget}
            onSuccess={() => ref.current?.reload()}
          />,
          <TermExport key="export" target={exchangeTarget} />,
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
