import { useRef, useState } from "react";
import { Button, Space, Tag, Typography, message, notification } from "antd";

import { get, put } from "../../request";
import { ActionType, ProList } from "@ant-design/pro-components";
import { renderBadge } from "../channel/ChannelTable";
import User, { IUser } from "../auth/User";
import { IChannel } from "../channel/Channel";
import { IStudio } from "../auth/Studio";
import TimeShow from "../general/TimeShow";
import {
  ITransferRequest,
  ITransferResponse,
  ITransferResponseList,
  ITransferStatus,
} from "../api/Transfer";
import { useIntl } from "react-intl";
import { BaseType } from "antd/lib/typography/Base";
import { TResType } from "../discussion/DiscussionListCard";

const { Text } = Typography;

interface ITransfer {
  id: string;
  origin_owner: IStudio;
  res_type: TResType;
  res_id: string;
  channel?: IChannel;
  transferor: IUser;
  new_owner: IStudio;
  status: ITransferStatus;
  editor?: IUser | null;
  created_at: string;
  updated_at: string;
}
interface IWidget {
  studioName?: string;
}
const TransferListWidget = ({ studioName }: IWidget) => {
  const ref = useRef<ActionType>();
  const [activeKey, setActiveKey] = useState<React.Key | undefined>("in");
  const [activeNumber, setActiveNumber] = useState<number>(0);
  const [closeNumber, setCloseNumber] = useState<number>(0);
  const intl = useIntl();

  const openNotification = (description: string) => {
    const args = {
      message: intl.formatMessage({
        id: `columns.studio.transfer.title`,
      }),
      description: description,
      duration: 0,
    };
    notification.open(args);
  };

  const setStatus = (status: ITransferStatus, id: string) => {
    const data: ITransferRequest = {
      status: status,
    };
    put<ITransferRequest, ITransferResponse>(`/v2/transfer/${id}`, data)
      .then((json) => {
        if (json.ok) {
          ref.current?.reload();
          openNotification(
            `已经` + intl.formatMessage({ id: `forms.status.${status}.label` })
          );
        } else {
          message.error(json.message);
        }
      })
      .catch((e) => {
        console.error(e);
      });
  };
  return (
    <>
      <ProList<ITransfer>
        rowKey="id"
        actionRef={ref}
        metas={{
          avatar: {
            render(dom, entity, index, action, schema) {
              return (
                <>
                  <User {...entity.transferor} showName={false} />
                </>
              );
            },
          },
          title: {
            render(dom, entity, index, action, schema) {
              return (
                <>
                  {entity.origin_owner.studioName}/{entity.channel?.name}
                </>
              );
            },
          },
          subTitle: {
            render(dom, entity, index, action, schema) {
              return <Tag>{entity.res_type}</Tag>;
            },
          },
          description: {
            search: false,
            render(dom, entity, index, action, schema) {
              return (
                <Space>
                  <User
                    key={"user"}
                    {...entity.transferor}
                    showAvatar={false}
                  />
                  <span key="text">{"transfer at"}</span>
                  <TimeShow key={"time"} createdAt={entity.created_at} />
                </Space>
              );
            },
          },
          content: {
            render(dom, entity, index, action, schema) {
              let style: BaseType | undefined;
              switch (entity.status) {
                case "accept":
                  style = "success";
                  break;
                case "refuse":
                  style = "warning";
                  break;
                case "cancel":
                  style = "danger";
                  break;
                default:
                  style = undefined;
                  break;
              }
              return (
                <Text type={style}>
                  {intl.formatMessage({
                    id: `forms.status.${entity.status}.label`,
                  })}
                </Text>
              );
            },
          },
          actions: {
            render: (text, row, index, action) => [
              activeKey === "in" ? (
                <>
                  <Button
                    type="text"
                    disabled={row.status !== "transferred"}
                    onClick={() => setStatus("accept", row.id)}
                  >
                    {intl.formatMessage({
                      id: `buttons.accept`,
                    })}
                  </Button>
                  <Button
                    disabled={row.status !== "transferred"}
                    danger
                    type="text"
                    onClick={() => setStatus("refuse", row.id)}
                  >
                    {intl.formatMessage({
                      id: `buttons.refuse`,
                    })}
                  </Button>
                </>
              ) : (
                <>
                  <Button
                    type="text"
                    disabled={row.status !== "transferred"}
                    onClick={() => setStatus("cancel", row.id)}
                  >
                    {intl.formatMessage({
                      id: `buttons.cancel`,
                    })}
                  </Button>
                </>
              ),
            ],
          },
        }}
        request={async (params = {}, sorter, filter) => {
          let url = `/v2/transfer?view=studio&name=${studioName}&view2=${activeKey}`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;
          url += params.keyword ? "&search=" + params.keyword : "";
          console.log("url", url);
          const res = await get<ITransferResponseList>(url);
          const items: ITransfer[] = res.data.rows.map((item, id) => {
            return item;
          });

          setActiveNumber(res.data.in);
          setCloseNumber(res.data.out);

          return {
            total: res.data.count,
            succcess: true,
            data: items,
          };
        }}
        bordered
        pagination={{
          showQuickJumper: true,
          showSizeChanger: true,
          pageSize: 10,
        }}
        search={false}
        options={{
          search: false,
        }}
        toolbar={{
          menu: {
            activeKey,
            items: [
              {
                key: "in",
                label: (
                  <span>
                    转入
                    {renderBadge(activeNumber, activeKey === "in")}
                  </span>
                ),
              },
              {
                key: "out",
                label: (
                  <span>
                    转出
                    {renderBadge(closeNumber, activeKey === "out")}
                  </span>
                ),
              },
            ],
            onChange(key) {
              setActiveKey(key);
              ref.current?.reload();
            },
          },
        }}
      />
    </>
  );
};

export default TransferListWidget;
