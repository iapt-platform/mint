import { useIntl } from "react-intl";
import { useEffect, useState } from "react";
import { ProList } from "@ant-design/pro-components";
import { UserAddOutlined } from "@ant-design/icons";
import { Space, Tag, Button, Layout, Popconfirm } from "antd";
import GroupAddMember from "./AddMember";
import { get } from "../../request";
import { IGroupMemberListResponse } from "../api/Group";
import { useAppSelector } from "../../hooks";
import { currentUser as _currentUser } from "../../reducers/current-user";

const { Content } = Layout;

interface IRoleTag {
  title: string;
  color: string;
}
interface DataItem {
  id: string;
  name?: string;
  tag: IRoleTag[];
  image: string;
}
interface IWidgetGroupFile {
  groupId?: string;
}
const Widget = ({ groupId }: IWidgetGroupFile) => {
  const intl = useIntl(); //i18n
  const [canDelete, setCanDelete] = useState(false);
  const [memberCount, setMemberCount] = useState<number>();

  return (
    <Content>
      <ProList<DataItem>
        rowKey="id"
        headerTitle={
          intl.formatMessage({ id: "group.member" }) +
          "-" +
          memberCount?.toString()
        }
        toolBarRender={() => {
          return [<GroupAddMember groupId={groupId} />];
        }}
        showActions="hover"
        request={async (params = {}, sorter, filter) => {
          // TODO
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
                setCanDelete(true);
                break;
              case "manager":
                setCanDelete(true);
                break;
            }
            const items: DataItem[] = res.data.rows.map((item, id) => {
              let member: DataItem = {
                id: item.user_id,
                name: item.user?.nickName,
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
              return <Space size={0}>{showtag}</Space>;
            },
          },
          actions: {
            render: (text, row, index, action) => [
              canDelete ? (
                <Popconfirm
                  title={intl.formatMessage({
                    id: "forms.message.member.delete",
                  })}
                  onConfirm={(
                    e?: React.MouseEvent<HTMLElement, MouseEvent>
                  ) => {
                    console.log("delete", row.id);
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

export default Widget;
