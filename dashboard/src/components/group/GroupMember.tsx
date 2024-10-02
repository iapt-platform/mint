import { useIntl } from "react-intl";
import { useRef, useState } from "react";
import { ActionType, ProList } from "@ant-design/pro-components";
import { Space, Tag, Button, Layout, Popconfirm } from "antd";

import GroupAddMember from "./AddMember";
import { delete_, get } from "../../request";
import {
  IGroupMemberDeleteResponse,
  IGroupMemberListResponse,
} from "../api/Group";
import User, { IUser } from "../auth/User";

const { Content } = Layout;

interface IRoleTag {
  title: string;
  color: string;
}
interface DataItem {
  id: number;
  userId: string;
  name?: string;
  user: IUser;
  tag: IRoleTag[];
  image: string;
}
interface IWidgetGroupFile {
  groupId?: string;
}
const GroupMemberWidget = ({ groupId }: IWidgetGroupFile) => {
  const intl = useIntl(); //i18n
  const [canManage, setCanManage] = useState(false);
  const [memberCount, setMemberCount] = useState<number>();

  const ref = useRef<ActionType>();
  return (
    <Content>
      <ProList<DataItem>
        rowKey="id"
        actionRef={ref}
        headerTitle={
          intl.formatMessage({ id: "group.member" }) +
          "-" +
          memberCount?.toString()
        }
        toolBarRender={() => {
          return [
            canManage ? (
              <GroupAddMember
                groupId={groupId}
                onCreated={() => {
                  ref.current?.reload();
                }}
              />
            ) : undefined,
          ];
        }}
        showActions="hover"
        request={async (params = {}, sorter, filter) => {
          console.log(params, sorter, filter);

          let url = `/v2/group-member?view=group&id=${groupId}`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;
          if (typeof params.keyword !== "undefined") {
            url += "&search=" + (params.keyword ? params.keyword : "");
          }
          const res = await get<IGroupMemberListResponse>(url);
          if (res.ok) {
            console.log(res.data.rows);
            setMemberCount(res.data.count);
            switch (res.data.role) {
              case "owner":
                setCanManage(true);
                break;
              case "manager":
                setCanManage(true);
                break;
            }
            const items: DataItem[] = res.data.rows.map((item, id) => {
              let member: DataItem = {
                id: item.id ? item.id : 0,
                userId: item.user_id,
                name: item.user?.nickName,
                user: item.user,
                tag: [],
                image: "",
              };
              switch (item.power) {
                case 0:
                  member.tag.push({ title: "拥有者", color: "success" });
                  break;
                case 1:
                  member.tag.push({ title: "管理员", color: "default" });
                  break;
              }

              return member;
            });
            console.log(items);
            return {
              total: res.data.count,
              succcess: true,
              data: items,
            };
          } else {
            console.error(res.message);
            return {
              total: 0,
              succcess: false,
              data: [],
            };
          }
        }}
        pagination={{
          showQuickJumper: true,
          showSizeChanger: true,
        }}
        metas={{
          title: {
            dataIndex: "name",
          },
          avatar: {
            dataIndex: "image",
            editable: false,
            render(dom, entity, index, action, schema) {
              return <User {...entity.user} showName={false} />;
            },
          },
          subTitle: {
            render: (text, row, index, action) => {
              const showtag = row.tag.map((item, id) => {
                return (
                  <Tag color={item.color} key={id}>
                    {item.title}
                  </Tag>
                );
              });
              return (
                <Space size={0} key={index}>
                  {showtag}
                </Space>
              );
            },
          },
          actions: {
            render: (text, row, index, action) => [
              canManage ? (
                <Popconfirm
                  key={index}
                  title={intl.formatMessage({
                    id: "forms.message.member.remove",
                  })}
                  onConfirm={(
                    e?: React.MouseEvent<HTMLElement, MouseEvent>
                  ) => {
                    console.log("delete", row.id);
                    delete_<IGroupMemberDeleteResponse>(
                      "/v2/group-member/" + row.id
                    ).then((json) => {
                      if (json.ok) {
                        console.log("delete ok");
                        ref.current?.reload();
                      }
                    });
                  }}
                  okText={intl.formatMessage({ id: "buttons.ok" })}
                  cancelText={intl.formatMessage({ id: "buttons.cancel" })}
                >
                  <Button size="small" type="link" danger key="link">
                    {intl.formatMessage({ id: "buttons.remove" })}
                  </Button>
                </Popconfirm>
              ) : (
                <></>
              ),
            ],
          },
        }}
      />
    </Content>
  );
};

export default GroupMemberWidget;
