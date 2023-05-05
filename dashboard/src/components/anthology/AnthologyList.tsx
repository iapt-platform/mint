import { ActionType, ProTable } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { Link } from "react-router-dom";
import { message, Modal, Typography } from "antd";
import { PlusOutlined } from "@ant-design/icons";
import { Button, Dropdown, Popover } from "antd";
import {
  ExclamationCircleOutlined,
  TeamOutlined,
  DeleteOutlined,
  EyeOutlined,
} from "@ant-design/icons";

import AnthologyCreate from "../../components/anthology/AnthologyCreate";
import {
  IAnthologyListResponse,
  IDeleteResponse,
} from "../../components/api/Article";
import { delete_, get } from "../../request";
import { PublicityValueEnum } from "../../components/studio/table";
import { useEffect, useRef, useState } from "react";
import ShareModal from "../share/ShareModal";
import { EResType } from "../share/Share";
import {
  IResNumberResponse,
  renderBadge,
} from "../../pages/studio/channel/list";
import StudioName, { IStudio } from "../auth/StudioName";

const { Text } = Typography;

interface IItem {
  sn: number;
  id: string;
  title: string;
  subtitle: string;
  publicity: number;
  articles: number;
  studio?: IStudio;
  createdAt: number;
}
interface IWidget {
  title?: string;
  studioName?: string;
  showCol?: string[];
  showCreate?: boolean;
  showOption?: boolean;
  onTitleClick?: Function;
}
const Widget = ({
  title,
  studioName,
  showCol,
  showCreate = true,
  showOption = true,
  onTitleClick,
}: IWidget) => {
  const intl = useIntl();
  const [openCreate, setOpenCreate] = useState(false);

  const [activeKey, setActiveKey] = useState<React.Key | undefined>("my");
  const [myNumber, setMyNumber] = useState<number>(0);
  const [collaborationNumber, setCollaborationNumber] = useState<number>(0);

  useEffect(() => {
    /**
     * 获取各种课程的数量
     */
    const url = `/v2/anthology-my-number?studio=${studioName}`;
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
          id: "message.delete.sure",
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
        return delete_<IDeleteResponse>(`/v2/anthology/${id}`)
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
      <ProTable<IItem>
        headerTitle={title}
        actionRef={ref}
        columns={[
          {
            title: intl.formatMessage({
              id: "dict.fields.sn.label",
            }),
            dataIndex: "sn",
            key: "sn",
            width: 50,
            search: false,
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.title.label",
            }),
            dataIndex: "title",
            key: "title",
            tip: "过长会自动收缩",
            ellipsis: true,
            render: (text, row, index, action) => {
              return (
                <div key={index}>
                  <div>
                    <Typography.Link
                      onClick={() => {
                        if (typeof onTitleClick !== "undefined") {
                          onTitleClick(row.id);
                        }
                      }}
                    >
                      {row.title}
                    </Typography.Link>
                  </div>
                  <Text type="secondary">{row.subtitle}</Text>
                </div>
              );
            },
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.owner.label",
            }),
            dataIndex: "studio",
            key: "studio",
            render: (text, row, index, action) => {
              return <StudioName data={row.studio} />;
            },
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.publicity.label",
            }),
            dataIndex: "publicity",
            key: "publicity",
            width: 100,
            search: false,
            filters: true,
            onFilter: true,
            valueEnum: PublicityValueEnum(),
          },
          {
            title: intl.formatMessage({
              id: "article.fields.article.count.label",
            }),
            dataIndex: "articles",
            key: "articles",
            width: 100,
            search: false,
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.created-at.label",
            }),
            key: "created-at",
            width: 100,
            search: false,
            dataIndex: "createdAt",
            valueType: "date",
            sorter: (a, b) => a.createdAt - b.createdAt,
          },
          {
            title: intl.formatMessage({ id: "buttons.option" }),
            key: "option",
            width: 120,
            hideInTable: !showOption,
            valueType: "option",
            render: (text, row, index, action) => [
              <Dropdown.Button
                key={index}
                type="link"
                trigger={["click", "contextMenu"]}
                menu={{
                  items: [
                    {
                      key: "open",
                      label: (
                        <Link to={`/anthology/${row.id}`}>
                          {intl.formatMessage({
                            id: "buttons.open.in.library",
                          })}
                        </Link>
                      ),
                      icon: <EyeOutlined />,
                    },
                    {
                      key: "share",
                      label: (
                        <ShareModal
                          trigger={intl.formatMessage({
                            id: "buttons.share",
                          })}
                          resId={row.id}
                          resType={EResType.collection}
                        />
                      ),
                      icon: <TeamOutlined />,
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
                      case "open":
                        window.open(`/anthology/${row.id}`, "_blank");
                        break;
                      case "share":
                        break;
                      case "remove":
                        showDeleteConfirm(row.id, row.title);
                        break;
                      default:
                        break;
                    }
                  },
                }}
              >
                <Link to={`/anthology/${row.id}`} target="_blank">
                  {intl.formatMessage({
                    id: "buttons.view",
                  })}
                </Link>
              </Dropdown.Button>,
            ],
          },
        ]}
        request={async (params = {}, sorter, filter) => {
          // TODO
          console.log(params, sorter, filter);
          let url = `/v2/anthology?view=studio&view2=${activeKey}&name=${studioName}`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;
          url += params.keyword ? "&search=" + params.keyword : "";
          url += sorter.createdAt
            ? "&order=created_at&dir=" +
              (sorter.createdAt === "ascend" ? "asc" : "desc")
            : "";

          const res = await get<IAnthologyListResponse>(url);
          const items: IItem[] = res.data.rows.map((item, id) => {
            const date = new Date(item.created_at);
            return {
              sn: id + 1,
              id: item.uid,
              title: item.title,
              subtitle: item.subtitle,
              publicity: item.status,
              articles: item.childrenNumber,
              studio: item.studio,
              createdAt: date.getTime(),
            };
          });
          console.log(items);
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
          pageSize: 10,
        }}
        search={false}
        options={{
          search: true,
        }}
        toolBarRender={() => [
          showCreate ? (
            <Popover
              content={
                <AnthologyCreate
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
            </Popover>
          ) : undefined,
        ]}
        toolbar={{
          menu: {
            activeKey,
            items: [
              {
                key: "my",
                label: (
                  <span>
                    此工作室的
                    {renderBadge(myNumber, activeKey === "my")}
                  </span>
                ),
              },
              {
                key: "collaboration",
                label: (
                  <span>
                    协作
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
              ref.current?.reload();
            },
          },
        }}
      />
    </>
  );
};

export default Widget;
