import { useEffect, useRef, useState } from "react";
import { useIntl } from "react-intl";
import { ActionType, ProList } from "@ant-design/pro-components";
import { Alert, Button } from "antd";
import { Badge, Dropdown, Space, Table, Typography } from "antd";
import {
  GlobalOutlined,
  EditOutlined,
  MoreOutlined,
  CopyOutlined,
  ReloadOutlined,
} from "@ant-design/icons";

import { IApiResponseChannelList, IFinal, TChannelType } from "../api/Channel";
import { post } from "../../request";
import { LockIcon } from "../../assets/icon";
import Studio, { IStudio } from "../auth/Studio";
import ProgressSvg from "./ProgressSvg";
import { IChannel } from "./Channel";
import { ArticleType } from "../article/Article";
import CopyToModal from "./CopyToModal";

const { Link, Text } = Typography;

interface IParams {
  owner?: string;
}

export interface IProgressRequest {
  sentence: string[];
  owner?: string;
}
export interface IItem {
  id: number;
  uid: string;
  title: string;
  summary: string;
  type: TChannelType;
  studio: IStudio;
  shareType: string;
  role?: string;
  publicity: number;
  final?: IFinal[];
  progress: number;
  createdAt: number;
  content_created_at?: string;
  content_updated_at?: string;
}
interface IWidget {
  type?: ArticleType | "editable";
  articleId?: string;
  multiSelect?: boolean /*是否支持多选*/;
  selectedKeys?: string[];
  reload?: boolean;
  disableChannelId?: string;
  defaultOwner?: string;
  onSelect?: Function;
}
const ChannelPickerTableWidget = ({
  type,
  articleId,
  multiSelect = true,
  selectedKeys = [],
  onSelect,
  disableChannelId,
  defaultOwner = "all",
  reload = false,
}: IWidget) => {
  const intl = useIntl();
  const [selectedRowKeys, setSelectedRowKeys] =
    useState<React.Key[]>(selectedKeys);
  const [showCheckBox, setShowCheckBox] = useState<boolean>(false);
  const [copyChannel, setCopyChannel] = useState<IChannel>();
  const [copyOpen, setCopyOpen] = useState<boolean>(false);
  const [ownerChanged, setOwnerChanged] = useState<boolean>(false);

  const ref = useRef<ActionType>();

  useEffect(() => {
    if (reload) {
      ref.current?.reload();
    }
  }, [reload]);

  return (
    <Space direction="vertical" style={{ width: "100%" }}>
      {defaultOwner !== "all" && ownerChanged === false ? (
        <Alert
          message={
            <>
              {"目前仅显示了版本"}
              <Text keyboard>
                {intl.formatMessage({ id: `buttons.channel.${defaultOwner}` })}
              </Text>
              {"可以点"}
              <Text keyboard>{"版本筛选"}</Text>
              {"显示其他版本"}
            </>
          }
          type="success"
          closable
          action={
            <Button
              type="link"
              onClick={() => {
                if (typeof onSelect !== "undefined") {
                  onSelect([]);
                }
              }}
            >
              不选择
            </Button>
          }
        />
      ) : undefined}
      <ProList<IItem, IParams>
        actionRef={ref}
        rowSelection={
          showCheckBox
            ? {
                // 自定义选择项参考: https://ant.design/components/table-cn/#components-table-demo-row-selection-custom
                // 注释该行则默认不显示下拉选项
                alwaysShowAlert: true,
                selectedRowKeys: selectedRowKeys,
                onChange: (selectedRowKeys: React.Key[]) => {
                  setSelectedRowKeys(selectedRowKeys);
                },
                selections: [Table.SELECTION_ALL, Table.SELECTION_INVERT],
              }
            : undefined
        }
        tableAlertRender={
          showCheckBox
            ? ({ selectedRowKeys, selectedRows, onCleanSelected }) => {
                return (
                  <Space>
                    {intl.formatMessage({ id: "buttons.selected" })}
                    <Badge color="geekblue" count={selectedRowKeys.length} />
                    <Link onClick={onCleanSelected}>
                      {intl.formatMessage({ id: "buttons.empty" })}
                    </Link>
                  </Space>
                );
              }
            : undefined
        }
        tableAlertOptionRender={
          showCheckBox
            ? ({ selectedRowKeys, selectedRows, onCleanSelected }) => {
                return (
                  <Space>
                    <Link
                      onClick={() => {
                        if (typeof onSelect !== "undefined") {
                          onSelect(
                            selectedRows.map((item) => {
                              return {
                                id: item.uid,
                                name: item.title,
                              };
                            })
                          );
                          setShowCheckBox(false);
                          ref.current?.reload();
                        }
                      }}
                    >
                      {intl.formatMessage({
                        id: "buttons.ok",
                      })}
                    </Link>
                    <Link
                      type="danger"
                      onClick={() => {
                        setShowCheckBox(false);
                      }}
                    >
                      {intl.formatMessage({
                        id: "buttons.cancel",
                      })}
                    </Link>
                  </Space>
                );
              }
            : undefined
        }
        request={async (params = {}, sorter, filter) => {
          console.log(params, sorter, filter);
          const sentElement = document.querySelectorAll(".pcd_sent");
          let sentList: string[] = [];
          for (let index = 0; index < sentElement.length; index++) {
            const element = sentElement[index];
            const id = element.id.split("_")[1];
            sentList.push(id);
          }
          const currOwner = params.owner ? params.owner : defaultOwner;
          if (params.owner) {
            setOwnerChanged(true);
          }
          console.log("owner", currOwner);
          const res = await post<IProgressRequest, IApiResponseChannelList>(
            `/v2/channel-progress`,
            {
              sentence: sentList,
              owner: currOwner,
            }
          );
          console.debug("progress data", res.data.rows);
          const items: IItem[] = res.data.rows
            .filter((value) => value.name.substring(0, 4) !== "_Sys")
            .map((item, id) => {
              const date = new Date(item.created_at);
              let all: number = 0;
              let finished: number = 0;
              item.final?.forEach((value) => {
                all += value[0];
                finished += value[1] ? value[0] : 0;
              });
              const progress = finished / all;
              return {
                id: id,
                uid: item.uid,
                title: item.name,
                summary: item.summary,
                studio: item.studio,
                shareType: "my",
                role: item.role,
                type: item.type,
                publicity: item.status,
                createdAt: date.getTime(),
                final: item.final,
                progress: progress,
              };
            });
          //当前被选择的
          const currChannel = items.filter((value) =>
            selectedRowKeys.includes(value.uid)
          );
          let show = selectedRowKeys;
          //有进度的
          const progressing = items.filter(
            (value) => value.progress > 0 && !show.includes(value.uid)
          );
          show = [...show, ...progressing.map((item) => item.uid)];
          //我自己的
          const myChannel = items.filter(
            (value) => value.role === "owner" && !show.includes(value.uid)
          );
          show = [...show, ...myChannel.map((item) => item.uid)];
          //其他的
          const others = items.filter(
            (value) => !show.includes(value.uid) && value.role !== "member"
          );
          setSelectedRowKeys(selectedRowKeys);
          const channelData = [
            ...currChannel,
            ...progressing,
            ...myChannel,
            ...others,
          ];
          return {
            total: res.data.count,
            succcess: true,
            data: channelData,
          };
        }}
        rowKey="uid"
        bordered
        options={false}
        search={{
          filterType: "light",
        }}
        toolBarRender={() => [
          multiSelect ? (
            <Button
              onClick={() => {
                setShowCheckBox(true);
              }}
            >
              选择
            </Button>
          ) : undefined,
          <Button
            type="link"
            onClick={() => {
              ref.current?.reload();
            }}
            icon={<ReloadOutlined />}
          />,
        ]}
        metas={{
          title: {
            render(dom, entity, index, action, schema) {
              let pIcon = <></>;
              switch (entity.publicity) {
                case 5:
                  pIcon = <LockIcon />;
                  break;
                case 10:
                  pIcon = <LockIcon />;
                  break;
                case 30:
                  pIcon = <GlobalOutlined />;
                  break;
              }

              return (
                <div
                  key={index}
                  style={{
                    width: "100%",
                    borderRadius: 5,
                    padding: "0 5px",
                    background:
                      selectedKeys.includes(entity.uid) && !showCheckBox
                        ? "linear-gradient(to left, rgb(63 255 165 / 54%), rgba(0, 0, 0, 0))"
                        : undefined,
                  }}
                >
                  <div
                    key="info"
                    style={{ overflowX: "clip", display: "flex" }}
                  >
                    <Space>
                      {pIcon}
                      {entity.role !== "member" ? <EditOutlined /> : undefined}
                    </Space>
                    <Button
                      type="link"
                      disabled={disableChannelId === entity.uid}
                      onClick={() => {
                        if (typeof onSelect !== "undefined") {
                          const e: IChannel = {
                            name: entity.title,
                            id: entity.uid,
                          };
                          onSelect([e]);
                        }
                      }}
                    >
                      <Space>
                        <Studio data={entity.studio} hideName />
                        {entity.title}
                      </Space>
                    </Button>
                  </div>
                  <div key="progress">
                    <ProgressSvg data={entity.final} width={200} />
                  </div>
                </div>
              );
            },
            search: false,
          },
          actions: {
            render: (dom, entity, index, action, schema) => {
              return (
                <Dropdown
                  key={index}
                  trigger={["click"]}
                  menu={{
                    items: [
                      {
                        key: "copy-to",
                        label: intl.formatMessage({
                          id: "buttons.copy.to",
                        }),
                        icon: <CopyOutlined />,
                      },
                    ],
                    onClick: (e) => {
                      switch (e.key) {
                        case "copy-to":
                          setCopyChannel({
                            id: entity.uid,
                            name: entity.title,
                            type: entity.type,
                          });
                          setCopyOpen(true);
                          break;

                        default:
                          break;
                      }
                    },
                  }}
                  placement="bottomRight"
                >
                  <Button
                    type="link"
                    size="small"
                    icon={<MoreOutlined />}
                  ></Button>
                </Dropdown>
              );
            },
          },
          owner: {
            // 自己扩展的字段，主要用于筛选，不在列表中显示
            title: "版本筛选",
            valueType: "select",
            valueEnum: {
              all: { text: intl.formatMessage({ id: "buttons.channel.all" }) },
              my: {
                text: intl.formatMessage({ id: "buttons.channel.my" }),
              },
              collaborator: {
                text: intl.formatMessage({
                  id: "buttons.channel.collaborator",
                }),
              },
              public: {
                text: intl.formatMessage({ id: "buttons.channel.public" }),
              },
            },
          },
        }}
      />
      <CopyToModal
        channel={copyChannel}
        open={copyOpen}
        onClose={() => setCopyOpen(false)}
      />
    </Space>
  );
};

export default ChannelPickerTableWidget;
