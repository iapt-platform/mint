import type { ActionType, ProColumns } from "@ant-design/pro-components";
import { EditableProTable, useRefFunction } from "@ant-design/pro-components";
import { Button, Dropdown, Form, Space, Typography } from "antd";
import React, { useEffect, useRef, useState } from "react";
import { useIntl } from "react-intl";

import ProjectEditDrawer from "./ProjectEditDrawer";
import type {
  IProjectData,
  IProjectListResponse,
  IProjectResponse,
  IProjectUpdateRequest,
} from "../../api/task";

import { get, post } from "../../request";
import { TaskBuilderProjectsModal } from "./TaskBuilderProjects";

const { Text } = Typography;
function generateUUID() {
  return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, function (c) {
    const r = (Math.random() * 16) | 0,
      v = c === "x" ? r : (r & 0x3) | 0x8;
    return v.toString(16);
  });
}

interface IWidget {
  studioName?: string;
  projectId?: string;
  onRowClick?: (data: IProjectData) => void;
  onSelect?: (id: string) => void;
}
const Project = ({ studioName, projectId, onRowClick, onSelect }: IWidget) => {
  const intl = useIntl();
  const [open, setOpen] = useState(false);
  const [editId, setEditId] = useState<string>();
  const [title, setTitle] = useState<React.ReactNode>();
  const [curr, setCurr] = useState<string>();
  const actionRef = useRef<ActionType | null>(null);
  const [editableKeys, setEditableRowKeys] = useState<React.Key[]>([]);
  const [dataSource, setDataSource] = useState<readonly IProjectData[]>([]);
  const [buildProjectsOpen, setBuildProjectsOpen] = useState(false);

  const [form] = Form.useForm();

  const ProjectTitle = ({ data }: { data?: IProjectData }) => (
    <Space>
      <Text strong>{data?.title}</Text>
      {data?.path?.reverse().map((item, id) => {
        return (
          <Text key={id} type="secondary">
            {" <"}
            <Button
              type="text"
              onClick={() => {
                if (onSelect) {
                  onSelect(item.id);
                }
              }}
            >
              {item.title}
            </Button>
          </Text>
        );
      })}
    </Space>
  );

  const loopDataSourceFilter = (
    data: readonly IProjectData[],
    id: React.Key | undefined
  ): IProjectData[] => {
    return data
      .map((item) => {
        if (item.id !== id) {
          if (item.children) {
            const newChildren = loopDataSourceFilter(item.children, id);
            return {
              ...item,
              children: newChildren.length > 0 ? newChildren : undefined,
            };
          }
          return item;
        }
        return null;
      })
      .filter(Boolean) as IProjectData[];
  };
  const removeRow = useRefFunction((record: IProjectData) => {
    setDataSource(loopDataSourceFilter(dataSource, record.id));
  });

  const columns: ProColumns<IProjectData>[] = [
    {
      title: intl.formatMessage({
        id: "forms.fields.title.label",
      }),
      dataIndex: "title",
      formItemProps: {
        rules: [
          {
            required: true,
            message: "此项为必填项",
          },
        ],
      },
      width: "30%",
      render: (_dom, record) => {
        return (
          <Button
            type="link"
            size="small"
            onClick={() => {
              if (onSelect) {
                onSelect(record.id);
              }
            }}
          >
            {record.title}
          </Button>
        );
      },
    },
    {
      title: intl.formatMessage({
        id: "forms.fields.milestone.label",
      }),
      key: "state",
      dataIndex: "state",
      readonly: true,
    },
    {
      title: intl.formatMessage({
        id: "forms.fields.status.label",
      }),
      key: "state",
      dataIndex: "state",
      readonly: true,
    },
    {
      title: "操作",
      valueType: "option",
      width: 250,
      render: (_text, record) => [
        <Button
          size="small"
          type="link"
          key="editable"
          onClick={() => {
            setEditId(record.id);
            setOpen(true);
          }}
        >
          编辑
        </Button>,
        record.type === "workflow" ? (
          <></>
        ) : (
          <EditableProTable.RecordCreator
            key="copy"
            parentKey={record.id}
            record={{
              id: generateUUID(),
              parent_id: record.id,
            }}
          >
            <Dropdown.Button
              size="small"
              type="link"
              menu={{
                items: [
                  {
                    key: "multi",
                    label: "批量添加",
                  },
                ],
                onClick: (e) => {
                  switch (e.key) {
                    case "multi":
                      setCurr(record.id);
                      setBuildProjectsOpen(true);
                      break;
                    default:
                      break;
                  }
                },
              }}
            >
              插入子节点
            </Dropdown.Button>
          </EditableProTable.RecordCreator>
        ),
        <Button
          type="link"
          danger
          size="small"
          key="delete"
          onClick={() => {
            removeRow(record);
          }}
        >
          删除
        </Button>,
      ],
    },
  ];

  const getChildren = (
    record: IProjectData,
    findIn: IProjectData[]
  ): IProjectData[] | undefined => {
    const children = findIn
      .filter((item) => item.parent?.id === record.id)
      .map((item) => {
        return { ...item, children: getChildren(item, findIn) };
      });
    if (children.length > 0) {
      return children;
    }
    return undefined;
  };

  useEffect(() => {
    actionRef.current?.reload();
  }, [projectId]);
  return (
    <>
      <TaskBuilderProjectsModal
        studioName={studioName}
        parentId={curr}
        open={buildProjectsOpen}
        onClose={() => setBuildProjectsOpen(false)}
        onDone={() => actionRef.current?.reload()}
      />
      <EditableProTable<IProjectData>
        onRow={(record) => ({
          onClick: () => {
            if (onRowClick) {
              onRowClick(record);
            }
          },
        })}
        rowKey="id"
        scroll={{
          x: 960,
        }}
        actionRef={actionRef}
        headerTitle={title}
        maxLength={5}
        search={false}
        // 关闭默认的新建按钮
        recordCreatorProps={false}
        columns={columns}
        request={async () => {
          const url = `/api/v2/project?view=project-tree&project_id=${projectId}`;
          console.info("api request", url);
          const res = await get<IProjectListResponse>(url);
          console.info("project api response", res);
          const root = res.data.rows
            .filter((item) => item.id === projectId)
            .map((item) => {
              return { ...item, children: getChildren(item, res.data.rows) };
            });
          return {
            data: root,
            total: res.data.count,
            success: res.ok,
          };
        }}
        value={dataSource}
        onChange={(value: readonly IProjectData[]) => {
          const root = value.find((item) => item.id === projectId);
          setTitle(ProjectTitle({ data: root }));
          setDataSource(value);
        }}
        editable={{
          form,
          editableKeys,
          onSave: async (_key, values) => {
            const data: IProjectUpdateRequest = {
              ...values,
              studio_name: studioName ?? "",
            };
            const url = `/api/v2/project`;
            console.info("save api request", url, data);
            const res = await post<IProjectUpdateRequest, IProjectResponse>(
              url,
              data
            );
            console.info("save api response", res);
          },

          onChange: setEditableRowKeys,
          actionRender: (_row, _config, dom) => [dom.save, dom.cancel],
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

export default Project;
