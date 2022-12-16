import { ProTable } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { Space, Table } from "antd";
import { GlobalOutlined } from "@ant-design/icons";
import { Button } from "antd";

import { PublicityValueEnum } from "../studio/table";
import { IApiResponseChannelList, IFinal } from "../api/Channel";
import { get } from "../../request";
import { LockIcon } from "../../assets/icon";
import StudioName, { IStudio } from "../auth/StudioName";
import ProgressSvg from "./ProgressSvg";
import { IChannel } from "./Channel";

export interface IItem {
  id: number;
  uid: string;
  title: string;
  summary: string;
  type: string;
  studio: IStudio;
  shareType: string;
  role?: string;
  publicity: number;
  createdAt: number;
  final?: IFinal[];
}
interface IWidget {
  type: string;
  articleId: string;
  onSelect?: Function;
}
const Widget = ({ type, articleId, onSelect }: IWidget) => {
  const intl = useIntl();

  return (
    <>
      <ProTable<IItem>
        columns={[
          {
            title: intl.formatMessage({
              id: "forms.fields.name.label",
            }),
            dataIndex: "title",
            key: "title",
            search: false,
            tip: "过长会自动收缩",
            ellipsis: true,
            valueType: "text",
            render: (text, row, index, action) => {
              let pIcon = <></>;
              switch (row.publicity) {
                case 10:
                  pIcon = <LockIcon />;
                  break;
                case 30:
                  pIcon = <GlobalOutlined />;
                  break;
              }
              return (
                <Button
                  type="link"
                  onClick={() => {
                    if (typeof onSelect !== "undefined") {
                      const e: IChannel = { name: row.title, id: row.uid };
                      onSelect(e);
                    }
                  }}
                >
                  <Space>
                    {pIcon}
                    {row.title}
                  </Space>
                </Button>
              );
            },
          },
          {
            title: intl.formatMessage({
              id: "channel.title",
            }),
            render: (text, row, index, action) => {
              return <StudioName data={row.studio} />;
            },
            key: "studio",
            search: false,
          },
          {
            title: intl.formatMessage({
              id: "tables.progress.label",
            }),
            width: 210,
            render: (text, row, index, action) => {
              return <ProgressSvg data={row.final} width={200} />;
            },
            key: "progress",
            search: false,
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.type.label",
            }),
            dataIndex: "type",
            key: "type",
            width: 100,
            search: false,
            filters: true,
            onFilter: true,
            valueEnum: {
              all: {
                text: intl.formatMessage({
                  id: "channel.type.all.title",
                }),
                status: "Default",
              },
              translation: {
                text: intl.formatMessage({
                  id: "channel.type.translation.label",
                }),
                status: "Success",
              },
              nissaya: {
                text: intl.formatMessage({
                  id: "channel.type.nissaya.label",
                }),
                status: "Processing",
              },
              commentary: {
                text: intl.formatMessage({
                  id: "channel.type.commentary.label",
                }),
                status: "Default",
              },
              original: {
                text: intl.formatMessage({
                  id: "channel.type.original.label",
                }),
                status: "Default",
              },
              general: {
                text: intl.formatMessage({
                  id: "channel.type.general.label",
                }),
                status: "Default",
              },
            },
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
            hideInTable: true,
            onFilter: true,
            valueEnum: PublicityValueEnum(),
          },
          {
            title: intl.formatMessage({ id: "buttons.option" }),
            key: "option",
            width: 120,
            valueType: "option",
            render: (text, row, index, action) => {
              return [
                intl.formatMessage({
                  id: "buttons.edit",
                }),
              ];
            },
          },
          {
            title: "类型",
            dataIndex: "shareType",
            valueType: "select",
            hideInTable: true,
            width: 120,
            valueEnum: {
              all: { text: "全部" },
              my: { text: "我的" },
              share: { text: "协作" },
              public: { text: "全网公开" },
            },
          },
          {
            title: intl.formatMessage({ id: "auth.role.label" }),
            dataIndex: "role",
            valueType: "select",
            width: 120,
            valueEnum: {
              all: { text: "全部" },
              owner: { text: intl.formatMessage({ id: "auth.role.owner" }) },
              manager: {
                text: intl.formatMessage({ id: "auth.role.manager" }),
              },
              editor: { text: intl.formatMessage({ id: "auth.role.editor" }) },
              member: { text: intl.formatMessage({ id: "auth.role.member" }) },
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
              <Button type="link">
                {intl.formatMessage({
                  id: "buttons.select",
                })}
              </Button>
            </Space>
          );
        }}
        request={async (params = {}, sorter, filter) => {
          // TODO
          console.log(params, sorter, filter);
          let url: string = "";
          switch (type) {
            case "chapter":
              const [book, para] = articleId.split("-");
              url = `/v2/channel?view=user-in-chapter&book=${book}&para=${para}&progress=sent`;
              break;
          }
          const res: IApiResponseChannelList = await get(url);
          console.log("data", res.data.rows);
          const items: IItem[] = res.data.rows.map((item, id) => {
            console.log("final", item.final);
            const date = new Date(item.created_at);
            return {
              id: id,
              uid: item.uid,
              title: item.name,
              summary: item.summary,
              studio: {
                id: item.studio.id,
                nickName: item.studio.nickName,
                studioName: item.studio.studioName,
                avatar: item.studio.avatar,
              },
              shareType: "my",
              role: item.role,
              type: item.type,
              publicity: item.status,
              createdAt: date.getTime(),
              final: item.final,
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
          showSizeChanger: false,
          pageSize: 5,
        }}
        options={false}
        search={{
          filterType: "light",
        }}
        toolBarRender={() => [<></>]}
      />
    </>
  );
};

export default Widget;
