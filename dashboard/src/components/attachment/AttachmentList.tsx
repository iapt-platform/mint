import { useIntl } from "react-intl";
import {
  Button,
  Space,
  Table,
  Dropdown,
  message,
  Modal,
  Typography,
  Image,
  Segmented,
} from "antd";
import {
  PlusOutlined,
  ExclamationCircleOutlined,
  FileOutlined,
  AudioOutlined,
  FileImageOutlined,
  MoreOutlined,
  BarsOutlined,
  AppstoreOutlined,
} from "@ant-design/icons";

import { ActionType, ProList } from "@ant-design/pro-components";

import { IUserDictDeleteRequest } from "../api/Dict";
import { delete_2, get, put } from "../../request";
import { useRef, useState } from "react";
import { IDeleteResponse } from "../api/Article";
import TimeShow from "../general/TimeShow";
import { getSorterUrl } from "../../utils";
import {
  IAttachmentListResponse,
  IAttachmentRequest,
  IAttachmentResponse,
  IAttachmentUpdate,
} from "../api/Attachments";
import { VideoIcon } from "../../assets/icon";
import AttachmentImport from "./AttachmentImport";
import VideoModal from "../general/VideoModal";
import FileSize from "../general/FileSize";

const { Text } = Typography;

export interface IAttachment {
  id: string;
  name: string;
  filename: string;
  title: string;
  size: number;
  content_type: string;
  url: string;
}
interface IParams {
  content_type?: string;
}
interface IWidget {
  studioName?: string;
  view?: "studio" | "all";
}
const AttachmentWidget = ({ studioName, view = "studio" }: IWidget) => {
  const intl = useIntl();
  const [replaceId, setReplaceId] = useState<string>();
  const [importOpen, setImportOpen] = useState(false);
  const [imgVisible, setImgVisible] = useState(false);
  const [imgPrev, setImgPrev] = useState<string>();
  const [list, setList] = useState("list");

  const [videoVisible, setVideoVisible] = useState(false);
  const [videoUrl, setVideoUrl] = useState<string>();

  const showDeleteConfirm = (id: string[], title: string) => {
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
        console.log("delete", id);
        return delete_2<IUserDictDeleteRequest, IDeleteResponse>(
          `/v2/userdict/${id}`,
          {
            id: JSON.stringify(id),
          }
        )
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
      <ProList<IAttachmentRequest, IParams>
        actionRef={ref}
        editable={{
          onSave: async (key, record, originRow) => {
            console.log(key, record, originRow);
            const url = `/v2/attachment/${key}`;
            const res = await put<IAttachmentUpdate, IAttachmentResponse>(url, {
              title: record.title,
            });
            return res.ok;
          },
        }}
        metas={{
          title: {
            dataIndex: "title",
            search: false,
            render: (dom, entity, index, action, schema) => {
              return (
                <Button
                  type="link"
                  onClick={() => {
                    const ct = entity.content_type.split("/");
                    switch (ct[0]) {
                      case "image":
                        setImgPrev(entity.url);
                        setImgVisible(true);
                        break;
                      case "video":
                        setVideoUrl(entity.url);
                        setVideoVisible(true);
                        break;
                      default:
                        break;
                    }
                  }}
                >
                  {entity.title}
                </Button>
              );
            },
          },
          description: {
            render: (dom, entity, index, action, schema) => {
              return (
                <Text type="secondary">
                  <Space>
                    {entity.content_type}
                    <FileSize size={entity.size} />
                    <TimeShow
                      type="secondary"
                      createdAt={entity.created_at}
                      updatedAt={entity.updated_at}
                    />
                  </Space>
                </Text>
              );
            },
            editable: false,
            search: false,
          },
          content: {
            editable: false,
            search: false,
            render: (dom, entity, index, action, schema) => {
              return list === "list" ? <></> : <Image src={entity.url} />;
            },
          },
          avatar: {
            editable: false,
            search: false,
            render: (dom, entity, index, action, schema) => {
              const ct = entity.content_type.split("/");
              let icon = <FileOutlined />;
              switch (ct[0]) {
                case "video":
                  icon = <VideoIcon />;
                  break;
                case "audio":
                  icon = <AudioOutlined />;
                  break;
                case "image":
                  icon = <FileImageOutlined />;

                  break;
              }
              return icon;
            },
          },
          actions: {
            render: (text, row, index, action) => {
              return [
                <Button
                  type="link"
                  size="small"
                  onClick={() => {
                    action?.startEditable(row.id);
                  }}
                >
                  编辑
                </Button>,
                <Dropdown
                  menu={{
                    items: [
                      { label: "替换", key: "replace" },
                      { label: "引用模版", key: "tpl" },
                    ],
                    onClick: (e) => {
                      console.log("click ", e.key);
                      switch (e.key) {
                        case "replace":
                          setReplaceId(row.id);
                          setImportOpen(true);
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
                    onClick={(e) => e.preventDefault()}
                  />
                </Dropdown>,
              ];
            },
          },
          content_type: {
            // 自己扩展的字段，主要用于筛选，不在列表中显示
            title: "类型",
            valueType: "select",
            valueEnum: {
              all: { text: "全部", status: "Default" },
              image: {
                text: "图片",
                status: "Error",
              },
              video: {
                text: "视频",
                status: "Success",
              },
              audio: {
                text: "音频",
                status: "Processing",
              },
            },
          },
        }}
        rowSelection={
          view === "all"
            ? undefined
            : {
                // 自定义选择项参考: https://ant.design/components/table-cn/#components-table-demo-row-selection-custom
                // 注释该行则默认不显示下拉选项
                selections: [Table.SELECTION_ALL, Table.SELECTION_INVERT],
              }
        }
        tableAlertRender={
          view === "all"
            ? undefined
            : ({ selectedRowKeys, selectedRows, onCleanSelected }) => (
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
        }
        tableAlertOptionRender={
          view === "all"
            ? undefined
            : ({ intl, selectedRowKeys, selectedRows, onCleanSelected }) => {
                return (
                  <Space size={16}>
                    <Button
                      type="link"
                      onClick={() => {
                        console.log(selectedRowKeys);
                        showDeleteConfirm(
                          selectedRowKeys.map((item) => item.toString()),
                          selectedRowKeys.length + "个单词"
                        );
                        onCleanSelected();
                      }}
                    >
                      批量删除
                    </Button>
                  </Space>
                );
              }
        }
        request={async (params = {}, sorter, filter) => {
          console.log(params, sorter, filter);
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);

          let url = "/v2/attachment?";
          switch (view) {
            case "studio":
              url += `view=studio&studio=${studioName}`;
              break;
            case "all":
              url += `view=all`;
              break;
            default:
              break;
          }
          url += `&limit=${params.pageSize}&offset=${offset}`;

          url += params.keyword ? "&search=" + params.keyword : "";
          if (params.content_type && params.content_type !== "all") {
            url += "&content_type=" + params.content_type;
          }

          url += getSorterUrl(sorter);

          console.log(url);
          const res = await get<IAttachmentListResponse>(url);
          return {
            total: res.data.count,
            success: res.ok,
            data: res.data.rows,
          };
        }}
        rowKey="id"
        bordered
        pagination={{
          showQuickJumper: true,
          showSizeChanger: true,
        }}
        search={{
          filterType: "light",
        }}
        options={{
          search: true,
        }}
        grid={list === "list" ? undefined : { gutter: 16, column: 3 }}
        headerTitle=""
        toolBarRender={() => [
          <Segmented
            options={[
              { label: "List", value: "list", icon: <BarsOutlined /> },
              {
                label: "Thumbnail",
                value: "thumbnail",
                icon: <AppstoreOutlined />,
              },
            ]}
            onChange={(value) => {
              console.log(value); // string
              setList(value.toString());
            }}
          />,
          <Button
            key="button"
            icon={<PlusOutlined />}
            type="primary"
            onClick={() => {
              setReplaceId(undefined);
              setImportOpen(true);
            }}
            disabled={view === "all"}
          >
            {intl.formatMessage({ id: "buttons.import" })}
          </Button>,
        ]}
      />
      <AttachmentImport
        replaceId={replaceId}
        open={importOpen}
        onOpenChange={(open: boolean) => {
          setImportOpen(open);
          ref.current?.reload();
        }}
      />

      <Image
        width={200}
        style={{ display: "none" }}
        preview={{
          visible: imgVisible,
          src: imgPrev,
          onVisibleChange: (value) => {
            setImgVisible(value);
          },
        }}
      />
      <VideoModal
        src={videoUrl}
        open={videoVisible}
        onOpenChange={(open: boolean) => setVideoVisible(open)}
      />
    </>
  );
};

export default AttachmentWidget;
