import { useEffect, useRef, useState } from "react";
import { useIntl } from "react-intl";
import { ActionType, ProList } from "@ant-design/pro-components";
import { Button } from "antd";
import { Badge, Dropdown, Space, Table, Typography } from "antd";
import {
  GlobalOutlined,
  EditOutlined,
  MoreOutlined,
  CopyOutlined,
} from "@ant-design/icons";

import { IApiResponseChannelList, IFinal, TChannelType } from "../api/Channel";
import { get, post } from "../../request";
import { LockIcon } from "../../assets/icon";
import StudioName, { IStudio } from "../auth/StudioName";
import ProgressSvg from "./ProgressSvg";
import { IChannel } from "./Channel";
import { ArticleType } from "../article/Article";
import CopyToModal from "./CopyToModal";

import { useAppSelector } from "../../hooks";
import { currentUser as _currentUser } from "../../reducers/current-user";
import { sentenceList } from "../../reducers/sentence";

const { Link } = Typography;

interface IProgressRequest {
  sentence: string[];
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
  createdAt: number;
  final?: IFinal[];
  progress: number;
}
interface IWidget {
  type?: ArticleType | "editable";
  articleId?: string;
  multiSelect?: boolean /*是否支持多选*/;
  selectedKeys?: string[];
  reload?: boolean;
  onSelect?: Function;
}
const Widget = ({
  type,
  articleId,
  multiSelect = true,
  selectedKeys = [],
  onSelect,
  reload = false,
}: IWidget) => {
  const intl = useIntl();
  const [selectedRowKeys, setSelectedRowKeys] =
    useState<React.Key[]>(selectedKeys);
  const [showCheckBox, setShowCheckBox] = useState<boolean>(false);
  const user = useAppSelector(_currentUser);
  const ref = useRef<ActionType>();
  const sentences = useAppSelector(sentenceList);

  useEffect(() => {
    if (reload) {
      ref.current?.reload();
    }
  }, [reload]);

  return (
    <>
      <ProList<IItem>
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
                console.log(selectedRowKeys);
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
                        console.log("select", selectedRowKeys);
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
          // TODO
          console.log(params, sorter, filter);
          let url: string = "";
          if (typeof articleId !== "undefined") {
            const id = articleId.split("_");
            const [book, para] = id[0].split("-");
            url = `/v2/channel-progress?view=user-in-chapter&book=${book}&para=${para}&progress=sent`;
          }
          const res = await post<IProgressRequest, IApiResponseChannelList>(
            url,
            {
              sentence: sentences,
            }
          );
          console.log("data", res.data.rows);
          const items: IItem[] = res.data.rows.map((item, id) => {
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
          console.log("user:", user);
          setSelectedRowKeys(selectedRowKeys);
          return {
            total: res.data.count,
            succcess: true,
            data: [...currChannel, ...progressing, ...myChannel, ...others],
          };
        }}
        rowKey="uid"
        bordered
        options={false}
        search={{
          filterType: "light",
        }}
        toolBarRender={() => [
          <Button
            onClick={() => {
              ref.current?.reload();
            }}
          >
            reload
          </Button>,
          multiSelect ? (
            <Button
              onClick={() => {
                setShowCheckBox(true);
                console.log("user:", user);
              }}
            >
              选择
            </Button>
          ) : undefined,
        ]}
        metas={{
          title: {
            render(dom, entity, index, action, schema) {
              let pIcon = <></>;
              switch (entity.publicity) {
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
                        ? "linear-gradient(to right,#006112,rgba(0,0,0,0))"
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
                        <StudioName data={entity.studio} showName={false} />
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
                        key: "copy_to",
                        label: (
                          <CopyToModal
                            trigger={intl.formatMessage({
                              id: "buttons.copy.to",
                            })}
                            channel={{
                              id: entity.uid,
                              name: entity.title,
                              type: entity.type,
                            }}
                          />
                        ),
                        icon: <CopyOutlined />,
                      },
                    ],
                    onClick: (e) => {
                      console.log("click ", e);
                      switch (e.key) {
                        case "copy_to":
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
          status: {
            // 自己扩展的字段，主要用于筛选，不在列表中显示
            title: "版本筛选",
            valueType: "select",
            valueEnum: {
              all: { text: "全部", status: "Default" },
              my: {
                text: "我的",
              },
              closed: {
                text: "协作",
              },
              processing: {
                text: "社区公开",
              },
            },
          },
        }}
      />
    </>
  );
};

export default Widget;
