import { useEffect, useRef, useState } from "react";
import { type ActionType, ProList } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { Link } from "react-router";
import { Button, Popover, Space } from "antd";

import { PlusOutlined } from "@ant-design/icons";

import { get } from "../../request";

import { getSorterUrl } from "../../utils";

import type {
  IProjectData,
  IProjectListResponse,
  TProjectType,
} from "../../api/task";
import ProjectCreate from "./ProjectCreate";
import ProjectEditDrawer from "./ProjectEditDrawer";
import User from "../auth/User";
import TimeShow from "../general/TimeShow";
import ShareModal from "../share/ShareModal";

import ProjectClone from "./ProjectClone";
import { EResType } from "../share/utils";

export interface IResNumberResponse {
  ok: boolean;
  message: string;
  data: {
    my: number;
    collaboration: number;
  };
}
export type TView = "current" | "studio" | "shared" | "community";
interface IWidget {
  studioName?: string;
  type?: TProjectType;
  view?: TView;
  readonly?: boolean;
  onSelect?: (data: IProjectData) => void;
}

const ProjectListWidget = ({
  studioName,
  view = "studio",
  type = "instance",
  readonly = false,
  onSelect,
}: IWidget) => {
  const intl = useIntl();
  const [openCreate, setOpenCreate] = useState(false);
  const [editId, setEditId] = useState<string>();
  const [open, setOpen] = useState(false);

  const ref = useRef<ActionType | null>(null);

  useEffect(() => {
    ref.current?.reload();
  }, [view]);

  return (
    <>
      <ProList<IProjectData>
        actionRef={ref}
        metas={{
          title: {
            render: (_text, row) => {
              return readonly ? (
                <>{row.title}</>
              ) : (
                <Link to={`/studio/${studioName}/task/project/${row.id}`}>
                  {row.title}
                </Link>
              );
            },
          },
          description: {
            dataIndex: "description",
            render(_dom, entity) {
              return (
                <Space>
                  <User {...entity.editor} showAvatar={false} />
                  <TimeShow
                    createdAt={entity.created_at}
                    updatedAt={entity.updated_at}
                  />
                </Space>
              );
            },
          },
          content: {
            dataIndex: "description",
          },
          subTitle: {
            render: () => {
              return <></>;
            },
          },
          actions: {
            render: (_text, row) => [
              <Button
                size="small"
                type="link"
                key="edit"
                onClick={() => {
                  setEditId(row.id);
                  setOpen(true);
                }}
              >
                {intl.formatMessage({
                  id: "buttons.edit",
                })}
              </Button>,
              <ProjectClone
                key="clone"
                projectId={row.id}
                studioName={studioName}
                trigger={
                  <Button size="small" type="link" key="clone">
                    {intl.formatMessage({
                      id: "buttons.clone",
                    })}
                  </Button>
                }
              />,
              <ShareModal
                key="share"
                trigger={
                  <Button type="link" size="small">
                    {intl.formatMessage({
                      id: "buttons.share",
                    })}
                  </Button>
                }
                resId={row.id}
                resType={EResType.workflow}
              />,
            ],
          },
        }}
        onRow={(record) => {
          return {
            onClick: () => {
              console.info(`点击了行：${record.title}`);
              onSelect?.(record);
            },
          };
        }}
        request={async (params = {}, sorter, filter) => {
          console.log(params, sorter, filter);
          let url = `/api/v2/project?view=${view}&type=${type}`;
          url += `&studio=${studioName}`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;

          url += params.keyword ? "&keyword=" + params.keyword : "";
          url += getSorterUrl(sorter);
          console.info("project list api request", url);
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
        toolbar={{
          actions: [
            view === "studio" ? (
              <Popover
                content={
                  <ProjectCreate
                    studio={studioName}
                    type={"workflow"}
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
              </Popover>
            ) : (
              <></>
            ),
          ],
        }}
      />
      <ProjectEditDrawer
        studioName={studioName}
        projectId={editId}
        openDrawer={open}
        onClose={() => setOpen(false)}
      />
    </>
  );
};

export default ProjectListWidget;
