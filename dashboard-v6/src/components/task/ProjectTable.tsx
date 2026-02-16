import { ActionType, ProTable } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { Link } from "react-router-dom";
import { Badge, Button, message, Modal, Popover } from "antd";
import { Dropdown } from "antd";
import {
  ExclamationCircleOutlined,
  DeleteOutlined,
  PlusOutlined,
} from "@ant-design/icons";

import { delete_, get } from "../../request";
import { PublicityValueEnum } from "../studio/table";
import { IDeleteResponse } from "../api/Article";
import { useEffect, useRef, useState } from "react";

import { getSorterUrl } from "../../utils";
import { TransferOutLinedIcon } from "../../assets/icon";
import { IProjectData, IProjectListResponse } from "../api/task";
import ProjectCreate from "./ProjectCreate";
import ShareModal from "../share/ShareModal";
import { EResType } from "../share/Share";

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

interface IWidget {
  studioName?: string;
  type?: string;
  disableChannels?: string[];
  onSelect?: Function;
}

const ProjectTableWidget = ({
  studioName,
  disableChannels,
  type,
  onSelect,
}: IWidget) => {
  const intl = useIntl();
  const [activeKey, setActiveKey] = useState<React.Key | undefined>("studio");
  const [openCreate, setOpenCreate] = useState(false);
  const [shareId, setShareId] = useState<string>();
  const [shareOpen, setShareOpen] = useState(false);

  useEffect(() => {
    ref.current?.reload();
  }, [disableChannels]);

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
            render(dom, entity, index, action, schema) {
              return (
                <Link to={`/studio/${studioName}/task/project/${entity.id}`}>
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
            render: (text, row, index, action) => {
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
                          showDeleteConfirm(row.id, row.title);
                          break;
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
          let url = `/v2/project?view=${activeKey}&type=instance`;
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
