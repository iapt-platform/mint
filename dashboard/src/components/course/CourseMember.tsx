import { useIntl } from "react-intl";
import { useRef, useState } from "react";
import { ActionType, ProList } from "@ant-design/pro-components";
import { Space, Tag, Button, Layout, Popconfirm } from "antd";

import CourseAddMember from "./AddMember";
import { delete_, get } from "../../request";

import {
  ICourseMemberDeleteResponse,
  ICourseMemberListResponse,
  TCourseMemberStatus,
} from "../api/Course";

const { Content } = Layout;

interface IRoleTag {
  title: string;
  color: string;
}

export interface ICourseMember {
  sn?: number;
  id?: string;
  userId: string;
  name?: string;
  tag: IRoleTag[];
  image: string;
  role?: string;
  startExp?: number;
  endExp?: number;
  currentExp?: number;
  expByDay?: number;
  status?: TCourseMemberStatus;
}
interface IWidget {
  courseId?: string;
}
const Widget = ({ courseId }: IWidget) => {
  const intl = useIntl(); //i18n
  const [canDelete, setCanDelete] = useState(false);
  const [memberCount, setMemberCount] = useState<number>();

  const ref = useRef<ActionType>();
  return (
    <Content>
      <ProList<ICourseMember>
        rowKey="id"
        actionRef={ref}
        headerTitle={
          intl.formatMessage({ id: "group.member" }) +
          "-" +
          memberCount?.toString()
        }
        toolBarRender={() => {
          return [
            canDelete ? (
              <CourseAddMember
                courseId={courseId}
                onCreated={() => {
                  ref.current?.reload();
                }}
              />
            ) : (
              <></>
            ),
          ];
        }}
        showActions="hover"
        request={async (params = {}, sorter, filter) => {
          // TODO
          console.log(params, sorter, filter);

          let url = `/v2/course-member?view=course&id=${courseId}`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;
          if (typeof params.keyword !== "undefined") {
            url += "&search=" + (params.keyword ? params.keyword : "");
          }
          const res = await get<ICourseMemberListResponse>(url);
          if (res.ok) {
            console.log(res.data);
            setMemberCount(res.data.count);
            switch (res.data.role) {
              case "owner":
                setCanDelete(true);
                break;
              case "manager":
                setCanDelete(true);
                break;
            }
            const items: ICourseMember[] = res.data.rows.map((item, id) => {
              let member: ICourseMember = {
                id: item.id ? item.id : "",
                userId: item.user_id,
                name: item.user?.nickName,
                tag: [],
                image: "",
              };
              member.tag.push({
                title: intl.formatMessage({
                  id: "forms.fields." + item.role + ".label",
                }),
                color: "default",
              });

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
                  placement="bottomLeft"
                  title={intl.formatMessage({
                    id: "forms.message.member.remove",
                  })}
                  onConfirm={(
                    e?: React.MouseEvent<HTMLElement, MouseEvent>
                  ) => {
                    console.log("delete", row.id);
                    delete_<ICourseMemberDeleteResponse>(
                      "/v2/course-member/" + row.id
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

export default Widget;
