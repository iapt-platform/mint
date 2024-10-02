import { ActionType, ProTable } from "@ant-design/pro-components";
import { FormattedMessage, useIntl } from "react-intl";
import { Link } from "react-router-dom";
import { Alert, Badge, message, Modal, Typography } from "antd";
import { Button, Dropdown, Popover } from "antd";
import {
  PlusOutlined,
  ExclamationCircleOutlined,
  DeleteOutlined,
  TeamOutlined,
} from "@ant-design/icons";

import ChannelCreate from "../../components/channel/ChannelCreate";
import { delete_, get } from "../../request";
import {
  IApiResponseChannelList,
  TChannelType,
} from "../../components/api/Channel";
import { PublicityValueEnum } from "../../components/studio/table";
import { IDeleteResponse } from "../../components/api/Article";
import { useEffect, useRef, useState } from "react";
import { TRole } from "../../components/api/Auth";
import ShareModal from "../../components/share/ShareModal";
import { EResType } from "../../components/share/Share";
import StudioName, { IStudio } from "../auth/Studio";
import StudioSelect from "../../components/channel/StudioSelect";
import { IChannel } from "./Channel";
import { getSorterUrl } from "../../utils";
import TransferCreate from "../transfer/TransferCreate";
import { TransferOutLinedIcon } from "../../assets/icon";

const { Text } = Typography;

export const channelTypeFilter = {
  all: {
    text: <FormattedMessage id="channel.type.all.title" />,
    status: "Default",
  },
  translation: {
    text: <FormattedMessage id="channel.type.translation.label" />,
    status: "Success",
  },
  nissaya: {
    text: <FormattedMessage id="channel.type.nissaya.label" />,
    status: "Processing",
  },
  commentary: {
    text: <FormattedMessage id="channel.type.commentary.label" />,
    status: "Default",
  },
  original: {
    text: <FormattedMessage id="channel.type.original.label" />,
    status: "Default",
  },
};

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
  type: TChannelType;
  role?: TRole;
  studio?: IStudio;
  publicity: number;
  created_at: string;
}

interface IWidget {
  studioName?: string;
  type?: string;
  disableChannels?: string[];
  channelType?: TChannelType;
  onSelect?: Function;
}

const ChannelTableWidget = ({
  studioName,
  disableChannels,
  channelType,
  type,
  onSelect,
}: IWidget) => {
  const intl = useIntl();

  const [openCreate, setOpenCreate] = useState(false);

  const [activeKey, setActiveKey] = useState<React.Key | undefined>("my");
  const [myNumber, setMyNumber] = useState<number>(0);
  const [collaborationNumber, setCollaborationNumber] = useState<number>(0);
  const [collaborator, setCollaborator] = useState<string>();
  const [transfer, setTransfer] = useState<string[]>();
  const [transferName, setTransferName] = useState<string>();
  const [transferOpen, setTransferOpen] = useState(false);

  useEffect(() => {
    ref.current?.reload();
  }, [disableChannels]);

  useEffect(() => {
    /**
     * 获取各种课程的数量
     */
    const url = `/v2/channel-my-number?studio=${studioName}`;
    console.log("url", url);
    get<IResNumberResponse>(url).then((json) => {
      if (json.ok) {
        setMyNumber(json.data.my);
        setCollaborationNumber(json.data.collaboration);
      }
    });
  }, [studioName]);

  const showDeleteConfirm = (id: string, title: string) => {
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
        const url = `/v2/channel/${id}`;
        console.log("delete api request", url);
        return delete_<IDeleteResponse>(url)
          .then((json) => {
            console.info("api response", json);
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
      {channelType ? (
        <Alert
          message={`仅显示版本类型${channelType}`}
          type="success"
          closable
        />
      ) : undefined}
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
            width: 250,
            key: "title",
            tooltip: "过长会自动收缩",
            ellipsis: true,
            render: (text, row, index, action) => {
              return (
                <>
                  <div key={1}>
                    <Button
                      disabled={disableChannels?.includes(row.uid)}
                      type="link"
                      key={index}
                      onClick={() => {
                        if (typeof onSelect !== "undefined") {
                          const channel: IChannel = {
                            name: row.title,
                            id: row.uid,
                            type: row.type,
                          };
                          onSelect(channel);
                        }
                      }}
                    >
                      {row.title}
                    </Button>
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
            tooltip: "过长会自动收缩",
            ellipsis: true,
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.role.label",
            }),
            dataIndex: "role",
            key: "role",
            width: 80,
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
            width: 80,
            search: false,
            filters: true,
            onFilter: true,
            valueEnum: channelTypeFilter,
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.publicity.label",
            }),
            dataIndex: "publicity",
            key: "publicity",
            width: 80,
            search: false,
            filters: true,
            onFilter: true,
            valueEnum: PublicityValueEnum(),
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.created-at.label",
            }),
            key: "created_at",
            width: 100,
            search: false,
            dataIndex: "created_at",
            valueType: "date",
            sorter: true,
          },
          {
            title: intl.formatMessage({ id: "buttons.option" }),
            key: "option",
            width: 100,
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
                        key: "transfer",
                        label: intl.formatMessage({
                          id: "columns.studio.transfer.title",
                        }),
                        icon: <TransferOutLinedIcon />,
                      },
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
                          showDeleteConfirm(row.uid, row.title);
                          break;
                        case "transfer":
                          setTransfer([row.uid]);
                          setTransferName(row.title);
                          setTransferOpen(true);
                          break;
                        default:
                          break;
                      }
                    },
                  }}
                >
                  <Link to={`/studio/${studioName}/channel/${row.uid}/setting`}>
                    {intl.formatMessage({
                      id: "buttons.setting",
                    })}
                  </Link>
                </Dropdown.Button>,
              ];
            },
          },
        ]}
        /*
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
        */
        request={async (params = {}, sorter, filter) => {
          console.log(params, sorter, filter);
          let url = `/v2/channel?view=studio&view2=${activeKey}&name=${studioName}`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;

          url += collaborator ? "&collaborator=" + collaborator : "";
          url += params.keyword ? "&search=" + params.keyword : "";
          url += channelType ? "&type=" + channelType : "";
          url += getSorterUrl(sorter);
          console.log("url", url);
          const res: IApiResponseChannelList = await get(url);
          const items: IChannelItem[] = res.data.rows.map((item, id) => {
            return {
              id: id + 1,
              uid: item.uid,
              title: item.name,
              summary: item.summary,
              type: item.type,
              role: item.role,
              studio: item.studio,
              publicity: item.status,
              created_at: item.created_at,
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
              studioName={studioName}
              onSelect={(value: string) => {
                setCollaborator(value);
                ref.current?.reload();
              }}
            />
          ) : undefined,
          <Popover
            content={
              <ChannelCreate
                studio={studioName}
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
                    {intl.formatMessage({ id: "labels.this-studio" })}
                    {renderBadge(myNumber, activeKey === "my")}
                  </span>
                ),
              },
              {
                key: "collaboration",
                label: (
                  <span>
                    {intl.formatMessage({ id: "labels.collaboration" })}
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
      <TransferCreate
        studioName={studioName}
        resId={transfer}
        resType="channel"
        resName={transferName}
        open={transferOpen}
        onOpenChange={(visible: boolean) => setTransferOpen(visible)}
      />
    </>
  );
};

export default ChannelTableWidget;
