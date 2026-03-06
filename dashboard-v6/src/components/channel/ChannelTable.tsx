import { type ActionType, ProTable } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { Link } from "react-router";
import { Alert, message, Modal, Progress, Typography } from "antd";
import { Button, Dropdown, Popover } from "antd";
import {
  PlusOutlined,
  ExclamationCircleOutlined,
  DeleteOutlined,
  TeamOutlined,
} from "@ant-design/icons";

import ChannelCreate from "./ChannelCreate";
import { delete_, get } from "../../../src/request";
import type {
  IApiResponseChannelList,
  IChannel,
  TChannelType,
} from "../../api/channel";
import { PublicityValueEnum } from "../studio/table";
import type { IDeleteResponse } from "../../../src/api/Article";
import { useEffect, useRef, useState } from "react";
import type { IStudio, TRole } from "../../../src/api/Auth";
import ShareModal from "../share/ShareModal";

import StudioName from "../../../src/components/auth/Studio";
import StudioSelect from "./StudioSelect";

import { getSorterUrl } from "../../../src/utils";
import TransferCreate from "../transfer/TransferCreate";
import { TransferOutLinedIcon } from "../../../src/assets/icon";
import { channelTypeFilter } from "./utils";
import StatusBadge from "../general/StatusBadge";
import { EResType } from "../share/utils";

const { Text } = Typography;

export interface IResNumberResponse {
  ok: boolean;
  message: string;
  data: {
    my: number;
    collaboration: number;
  };
}

export interface IChapter {
  book: number;
  paragraph: number;
}

interface IChannelItem {
  id: number;
  uid: string;
  title: string;
  summary: string;
  type: TChannelType;
  role?: TRole;
  studio?: IStudio;
  publicity: number;
  progress?: number;
  created_at: string;
}

interface IWidget {
  studioName?: string;
  type?: string;
  disableChannels?: string[];
  channelType?: TChannelType;
  chapter?: IChapter;
  onSelect?: (channel: IChannel) => void;
}

const ChannelTableWidget = ({
  studioName,
  disableChannels,
  channelType,
  chapter,
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
     * 获取各种channel的数量
     */
    const url = `/api/v2/channel-my-number?studio=${studioName}`;
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
        const url = `/api/v2/channel/${id}`;
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

  const ref = useRef<ActionType | null>(null);

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
            render: (_text, row, index) => {
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
              id: "forms.fields.created-at.label",
            }),
            key: "progress",
            hideInTable: typeof chapter === "undefined",
            render(_dom, entity) {
              return (
                <Progress
                  size="small"
                  percent={Math.floor((entity.progress ?? 0) * 100)}
                  style={{ width: 150 }}
                />
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
            valueEnum: channelTypeFilter(intl),
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
            hideInTable: activeKey !== "my",
            render: (_text, row, index) => {
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
                  <Link to={`/workspace/channel/${row.uid}/setting`}>
                    {intl.formatMessage({
                      id: "buttons.setting",
                    })}
                  </Link>
                </Dropdown.Button>,
              ];
            },
          },
        ]}
        request={async (params = {}, sorter, filter) => {
          console.log(params, sorter, filter);
          let url = `/api/v2/channel?`;
          if (activeKey === "community") {
            url += `view=public`;
          } else {
            url += `view=studio&view2=${activeKey}&name=${studioName}`;
          }
          if (chapter) {
            url += `&book=${chapter.book}&paragraph=${chapter.paragraph}`;
          }
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;

          url += collaborator ? "&collaborator=" + collaborator : "";
          url += params.keyword ? "&search=" + params.keyword : "";
          url += channelType ? "&type=" + channelType : "";
          if (chapter && activeKey === "community") {
            url += "&order=progress";
          } else {
            url += getSorterUrl(sorter);
          }

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
              progress: item.progress,
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
                    <StatusBadge count={myNumber} active={activeKey === "my"} />
                  </span>
                ),
              },
              {
                key: "collaboration",
                label: (
                  <span>
                    {intl.formatMessage({ id: "labels.collaboration" })}
                    <StatusBadge
                      count={collaborationNumber}
                      active={activeKey === "collaboration"}
                    />
                  </span>
                ),
              },
              {
                key: "community",
                label: (
                  <span>
                    {intl.formatMessage({ id: "labels.community" })}
                    <StatusBadge
                      count={collaborationNumber}
                      active={activeKey === "community"}
                    />
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
