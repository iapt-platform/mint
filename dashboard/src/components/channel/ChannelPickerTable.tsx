import { ProList } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { Dropdown, Space, Table } from "antd";
import {
  GlobalOutlined,
  EditOutlined,
  MoreOutlined,
  CopyOutlined,
} from "@ant-design/icons";
import { Button } from "antd";

import { IApiResponseChannelList, IFinal, TChannelType } from "../api/Channel";
import { get } from "../../request";
import { LockIcon } from "../../assets/icon";
import StudioName, { IStudio } from "../auth/StudioName";
import ProgressSvg from "./ProgressSvg";
import { IChannel } from "./Channel";
import { ArticleType } from "../article/Article";
import CopyToModal from "./CopyToModal";
import { useState } from "react";

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
}
interface IWidget {
  type?: ArticleType | "editable";
  articleId?: string;
  multiSelect?: boolean;
  selectedKeys?: string[];
  onSelect?: Function;
}
const Widget = ({
  type,
  articleId,
  multiSelect = true,
  selectedKeys = [],
  onSelect,
}: IWidget) => {
  const intl = useIntl();
  const [selectedRowKeys, setSelectedRowKeys] =
    useState<React.Key[]>(selectedKeys);
  return (
    <>
      <ProList<IItem>
        rowSelection={
          multiSelect
            ? {
                // 自定义选择项参考: https://ant.design/components/table-cn/#components-table-demo-row-selection-custom
                // 注释该行则默认不显示下拉选项
                selectedRowKeys,
                selections: [Table.SELECTION_ALL, Table.SELECTION_INVERT],
              }
            : undefined
        }
        tableAlertRender={
          multiSelect
            ? ({ selectedRowKeys, selectedRows, onCleanSelected }) => (
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
            : undefined
        }
        tableAlertOptionRender={
          multiSelect
            ? ({ selectedRowKeys, selectedRows, onCleanSelected }) => {
                return (
                  <Space size={16}>
                    <Button
                      type="link"
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
                        }
                      }}
                    >
                      {intl.formatMessage({
                        id: "buttons.select",
                      })}
                    </Button>
                  </Space>
                );
              }
            : undefined
        }
        request={async (params = {}, sorter, filter) => {
          // TODO
          console.log(params, sorter, filter);
          let url: string = "";
          switch (type) {
            case "editable":
              url = `/v2/channel?view=user-edit`;
              break;
            case "chapter":
              if (typeof articleId !== "undefined") {
                const id = articleId.split("_");
                const [book, para] = id[0].split("-");
                url = `/v2/channel?view=user-in-chapter&book=${book}&para=${para}&progress=sent`;
              }

              break;
          }
          const res: IApiResponseChannelList = await get(url);
          console.log("data", res.data.rows);
          const items: IItem[] = res.data.rows.map((item, id) => {
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
          setSelectedRowKeys(selectedRowKeys);
          return {
            total: res.data.count,
            succcess: true,
            data: items,
          };
        }}
        rowKey="uid"
        bordered
        options={false}
        search={{
          filterType: "light",
        }}
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
                <div key={index}>
                  <div key="info">
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
