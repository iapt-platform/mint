import { type ActionType, ProTable } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { Link } from "react-router";
import { Button, Popover } from "antd";
import { Dropdown } from "antd";
import { PlusOutlined } from "@ant-design/icons";

import { get } from "../../request";
import { PublicityValueEnum } from "../studio/table";

import { useEffect, useRef, useState } from "react";

import { getSorterUrl } from "../../utils";
import { TransferOutLinedIcon } from "../../assets/icon";
import type { IProjectData, IProjectListResponse } from "../../api/task";
import ProjectCreate from "./ProjectCreate";
import ShareModal from "../share/ShareModal";
import { EResType } from "../share/utils";

export interface IResNumberResponse {
  ok: boolean;
  message: string;
  data: {
    my: number;
    collaboration: number;
  };
}

interface IWidget {
  studioName?: string;
  type?: string;
  disableChannels?: string[];
}

const ProjectTableWidget = ({ studioName, disableChannels }: IWidget) => {
  const intl = useIntl();
  const [activeKey, setActiveKey] = useState<React.Key | undefined>("studio");
  const [openCreate, setOpenCreate] = useState(false);
  const [shareId, setShareId] = useState<string>();
  const [shareOpen, setShareOpen] = useState(false);

  useEffect(() => {
    ref.current?.reload();
  }, [disableChannels]);

  const ref = useRef<ActionType | null>(null);

  return (
    <>
      {shareId ? (
        <ShareModal
          open={shareOpen}
          onClose={() => setShareOpen(false)}
          resId={shareId}
          resType={EResType.project}
        />
      ) : (
        <></>
      )}

      <ProTable<IProjectData>
        actionRef={ref}
        columns={[
          {
            title: intl.formatMessage({
              id: "forms.fields.title.label",
            }),
            dataIndex: "title",
            width: 250,
            key: "title",
            tooltip: "过长会自动收缩",
            ellipsis: true,
            render(_dom, entity) {
              return (
                <Link to={`/workspace/task/project/${entity.id}`}>
                  {entity.title}
                </Link>
              );
            },
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.executors.label",
            }),
            dataIndex: "executors",
            key: "executors",
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.milestone.label",
            }),
            dataIndex: "milestone",
            key: "milestone",
            width: 80,
            search: false,
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.status.label",
            }),
            dataIndex: "status",
            key: "status",
            width: 80,
            search: false,
            filters: true,
            onFilter: true,
            valueEnum: PublicityValueEnum(),
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.updated-at.label",
            }),
            key: "updated_at",
            width: 100,
            search: false,
            dataIndex: "updated_at",
            valueType: "date",
            sorter: true,
          },
          {
            title: intl.formatMessage({ id: "buttons.option" }),
            key: "option",
            width: 100,
            valueType: "option",
            render: (_text, row, index) => {
              return [
                <Dropdown.Button
                  key={index}
                  type="link"
                  trigger={["click", "contextMenu"]}
                  menu={{
                    items: [
                      {
                        key: "transfer",
                        label: intl.formatMessage({
                          id: "columns.studio.transfer.title",
                        }),
                        icon: <TransferOutLinedIcon />,
                      },
                      {
                        key: "share",
                        label: intl.formatMessage({
                          id: "buttons.share",
                        }),
                      },
                      // FIXME: 删除项目暂时隐藏。原来从 ChannelTable 复制来没改，删项目调的是
                      // DELETE /api/v2/channel/{项目 id}，永远删不掉；而 v2 的 ProjectController@destroy
                      // 是空方法，改调它也删不了。迁任务 / 项目到 v3 时补上删除（含软删除，projects
                      // 表缺 deleted_at 列；子项目与任务怎么处理也要定），再把这一项加回来。
                    ],
                    onClick: (e) => {
                      switch (e.key) {
                        case "share":
                          setShareId(row.id);
                          setShareOpen(true);
                          break;
                        default:
                          break;
                      }
                    },
                  }}
                >
                  {/* FIXME: 同样是从 ChannelTable 复制来没改——指向 channel 设置页的旧路由（v6 没有），
                      应改成项目的设置页；迁任务 / 项目到 v3 时一并处理 */}
                  <Link to={`/studio/${studioName}/channel/${row.id}/setting`}>
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
          let url = `/api/v2/project?view=${activeKey}&type=instance`;
          url += `&studio=${studioName}`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;

          url += params.keyword ? "&keyword=" + params.keyword : "";
          url += getSorterUrl(sorter);
          console.log("project list api request", url);
          const res = await get<IProjectListResponse>(url);
          console.info("project list api response", res);
          return {
            total: res.data.count,
            succcess: res.ok,
            data: res.data.rows,
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
          <Popover
            content={
              <ProjectCreate
                studio={studioName}
                type={"instance"}
                onCreate={() => {
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
                key: "studio",
                label: "我的项目",
              },
              {
                key: "shared",
                label: intl.formatMessage({ id: "labels.shared" }),
              },
            ],
            onChange(key) {
              console.log("show course", key);
              setActiveKey(key);
              ref.current?.reload();
            },
          },
        }}
      />
    </>
  );
};

export default ProjectTableWidget;
