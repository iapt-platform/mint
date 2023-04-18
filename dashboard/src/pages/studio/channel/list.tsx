import { useParams } from "react-router-dom";
import { ActionType, ProTable } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { Link } from "react-router-dom";
import { Badge, message, Modal, Space, Table, Typography } from "antd";
import { Button, Dropdown, Popover } from "antd";
import {
  PlusOutlined,
  ExclamationCircleOutlined,
  DeleteOutlined,
  TeamOutlined,
} from "@ant-design/icons";

import ChannelCreate from "../../../components/channel/ChannelCreate";
import { delete_, get } from "../../../request";
import { IApiResponseChannelList } from "../../../components/api/Channel";
import { PublicityValueEnum } from "../../../components/studio/table";
import { IDeleteResponse } from "../../../components/api/Article";
import { useEffect, useRef, useState } from "react";
import { TRole } from "../../../components/api/Auth";
import ShareModal from "../../../components/share/ShareModal";
import { EResType } from "../../../components/share/Share";
import StudioName, { IStudio } from "../../../components/auth/StudioName";
import StudioSelect from "../../../components/channel/StudioSelect";
const { Text } = Typography;

export interface IResNumberResponse {
  ok: boolean;
  message: string;
  data: {
    my: number;
    collaboration: number;
  };
}

export const renderBadge = (count: number, active = false) => {
  return (
    <Badge
      count={count}
      style={{
        marginBlockStart: -2,
        marginInlineStart: 4,
        color: active ? "#1890FF" : "#999",
        backgroundColor: active ? "#E6F7FF" : "#eee",
      }}
    />
  );
};

interface IChannelItem {
  id: number;
  uid: string;
  title: string;
  summary: string;
  type: string;
  role?: TRole;
  studio?: IStudio;
  publicity: number;
  createdAt: number;
}

const Widget = () => {
  const intl = useIntl();
  const { studioname } = useParams();
  const [openCreate, setOpenCreate] = useState(false);

  const [activeKey, setActiveKey] = useState<React.Key | undefined>("my");
  const [myNumber, setMyNumber] = useState<number>(0);
  const [collaborationNumber, setCollaborationNumber] = useState<number>(0);
  const [collaborator, setCollaborator] = useState<string>();
  useEffect(() => {
    /**
     * 获取各种课程的数量
     */
    const url = `/v2/channel-my-number?studio=${studioname}`;
    console.log("url", url);
    get<IResNumberResponse>(url).then((json) => {
      if (json.ok) {
        setMyNumber(json.data.my);
        setCollaborationNumber(json.data.collaboration);
      }
    });
  }, [studioname]);

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
        return delete_<IDeleteResponse>(`/v2/channel/${id}`)
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
      <ProTable<IChannelItem>
        actionRef={ref}
        columns={[
          {
            title: intl.formatMessage({
              id: "dict.fields.sn.label",
            }),
            dataIndex: "id",
            key: "id",
            width: 50,
            search: false,
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.title.label",
            }),
            dataIndex: "title",
            key: "title",
            tip: "过长会自动收缩",
            ellipsis: true,
            render: (text, row, index, action) => {
              return (
                <>
                  <div key={1}>
                    <Link
                      to={`/studio/${studioname}/channel/${row.uid}`}
                      key={index}
                    >
                      {row.title}
                    </Link>
                  </div>
                  {activeKey !== "my" ? (
                    <div key={3}>
                      <Text type="secondary">
                        <StudioName data={row.studio} />
                      </Text>
                    </div>
                  ) : undefined}
                </>
              );
            },
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.summary.label",
            }),
            dataIndex: "summary",
            key: "summary",
            tip: "过长会自动收缩",
            ellipsis: true,
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.role.label",
            }),
            dataIndex: "role",
            key: "role",
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
              owner: {
                text: intl.formatMessage({
                  id: "auth.role.owner",
                }),
              },
              manager: {
                text: intl.formatMessage({
                  id: "auth.role.manager",
                }),
              },
              editor: {
                text: intl.formatMessage({
                  id: "auth.role.editor",
                }),
              },
              member: {
                text: intl.formatMessage({
                  id: "auth.role.member",
                }),
              },
            },
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
            onFilter: true,
            valueEnum: PublicityValueEnum(),
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
                  trigger={["click", "contextMenu"]}
                  menu={{
                    items: [
                      {
                        key: "share",
                        label: (
                          <ShareModal
                            trigger={intl.formatMessage({
                              id: "buttons.share",
                            })}
                            resId={row.uid}
                            resType={EResType.channel}
                          />
                        ),
                        icon: <TeamOutlined />,
                      },
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
                          showDeleteConfirm(row.uid, row.title);
                          break;
                        default:
                          break;
                      }
                    },
                  }}
                >
                  <Link to={`/studio/${studioname}/channel/${row.uid}/edit`}>
                    {intl.formatMessage({
                      id: "buttons.edit",
                    })}
                  </Link>
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
              <Button type="link">
                {intl.formatMessage({
                  id: "buttons.delete.all",
                })}
              </Button>
            </Space>
          );
        }}
        request={async (params = {}, sorter, filter) => {
          // TODO
          console.log(params, sorter, filter);
          let url = `/v2/channel?view=studio&view2=${activeKey}&name=${studioname}`;
          url += collaborator ? "&collaborator=" + collaborator : "";
          url += params.keyword ? "&search=" + params.keyword : "";

          console.log("url", url);
          const res: IApiResponseChannelList = await get(url);
          const items: IChannelItem[] = res.data.rows.map((item, id) => {
            const date = new Date(item.created_at);
            return {
              id: id,
              uid: item.uid,
              title: item.name,
              summary: item.summary,
              type: item.type,
              role: item.role,
              studio: item.studio,
              publicity: item.status,
              createdAt: date.getTime(),
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
        search={false}
        options={{
          search: true,
        }}
        toolBarRender={() => [
          activeKey !== "my" ? (
            <StudioSelect
              studioName={studioname}
              onSelect={(value: string) => {
                setCollaborator(value);
                ref.current?.reload();
              }}
            />
          ) : undefined,
          <Popover
            content={
              <ChannelCreate
                studio={studioname}
                onSuccess={() => {
                  setOpenCreate(false);
                  ref.current?.reload();
                }}
              />
            }
            placement="bottomRight"
            trigger="click"
            open={openCreate}
            onOpenChange={(open: boolean) => {
              setOpenCreate(open);
            }}
          >
            <Button key="button" icon={<PlusOutlined />} type="primary">
              {intl.formatMessage({ id: "buttons.create" })}
            </Button>
          </Popover>,
        ]}
        toolbar={{
          menu: {
            activeKey,
            items: [
              {
                key: "my",
                label: (
                  <span>
                    此工作室的
                    {renderBadge(myNumber, activeKey === "my")}
                  </span>
                ),
              },
              {
                key: "collaboration",
                label: (
                  <span>
                    协作
                    {renderBadge(
                      collaborationNumber,
                      activeKey === "collaboration"
                    )}
                  </span>
                ),
              },
            ],
            onChange(key) {
              console.log("show course", key);
              setActiveKey(key);
              setCollaborator(undefined);
              ref.current?.reload();
            },
          },
        }}
      />
    </>
  );
};

export default Widget;
