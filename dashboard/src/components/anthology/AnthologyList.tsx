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
import Share, { EResType } from "../share/Share";

import StudioName, { IStudio } from "../auth/Studio";
import { IResNumberResponse, renderBadge } from "../channel/ChannelTable";
import { fullUrl, getSorterUrl } from "../../utils";

const { Text } = Typography;

interface IItem {
  sn: number;
  id: string;
  title: string;
  subtitle: string;
  publicity: number;
  articles: number;
  studio?: IStudio;
  updated_at: string;
}
interface IWidget {
  title?: string;
  studioName?: string;
  showCol?: string[];
  showCreate?: boolean;
  showOption?: boolean;
  onTitleClick?: Function;
}
const AnthologyListWidget = ({
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

  const [isModalOpen, setIsModalOpen] = useState(false);
  const [shareResId, setShareResId] = useState<string>("");
  const [shareResType, setShareResType] = useState<EResType>(
    EResType.collection
  );
  const showShareModal = (resId: string, resType: EResType) => {
    setShareResId(resId);
    setShareResType(resType);
    setIsModalOpen(true);
  };

  const handleOk = () => {
    setIsModalOpen(false);
  };

  const handleCancel = () => {
    setIsModalOpen(false);
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
            tooltooltip: "过长会自动收缩",
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
                      label: intl.formatMessage({
                        id: "buttons.share",
                      }),
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
                        window.open(fullUrl(`/anthology/${row.id}`), "_blank");
                        break;
                      case "share":
                        console.log("share");
                        showShareModal(row.id, EResType.collection);
                        break;
                      case "remove":
                        showDeleteConfirm(row.id, row.title);
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
          console.log(params, sorter, filter);
          let url = `/v2/anthology?view=studio&view2=${activeKey}&name=${studioName}`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;
          url += params.keyword ? "&search=" + params.keyword : "";

          url += getSorterUrl(sorter);

          const res = await get<IAnthologyListResponse>(url);
          const items: IItem[] = res.data.rows.map((item, id) => {
            return {
              sn: id + offset + 1,
              id: item.uid,
              title: item.title,
              subtitle: item.subtitle,
              publicity: item.status,
              articles: item.childrenNumber,
              studio: item.studio,
              updated_at: item.updated_at,
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
                    {intl.formatMessage({ id: "labels.this-studio" })}
                    {renderBadge(myNumber, activeKey === "my")}
                  </span>
                ),
              },
              {
                key: "collaboration",
                label: (
                  <span>
                    {intl.formatMessage({ id: "labels.collaboration" })}
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

      <Modal
        destroyOnClose={true}
        width={700}
        title={intl.formatMessage({ id: "labels.collaboration" })}
        open={isModalOpen}
        onOk={handleOk}
        onCancel={handleCancel}
      >
        <Share resId={shareResId} resType={shareResType} />
      </Modal>
    </>
  );
};

export default AnthologyListWidget;
