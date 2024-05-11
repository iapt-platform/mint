import { useEffect, useRef } from "react";
import { useIntl } from "react-intl";
import { Space, Tag, Typography } from "antd";
import { ActionType, ProList } from "@ant-design/pro-components";

import { get } from "../../request";
import { ICourseMemberData, ICourseMemberListResponse } from "../api/Course";
import User from "../auth/User";
import TimeShow from "../general/TimeShow";
import { getStatusColor } from "./RolePower";

const { Text } = Typography;

interface IParams {
  timeline?: string;
}
interface IWidget {
  courseId?: string;
  userId?: string;
}

const CourseMemberTimeLineWidget = ({ courseId, userId }: IWidget) => {
  const intl = useIntl(); //i18n

  const ref = useRef<ActionType>();

  useEffect(() => {
    ref.current?.reload();
  }, [courseId, userId]);

  return (
    <>
      <ProList<ICourseMemberData, IParams>
        actionRef={ref}
        search={{
          filterType: "light",
        }}
        metas={{
          avatar: {
            render(dom, entity, index, action, schema) {
              return <User {...entity.user} showName={false} />;
            },
            editable: false,
          },
          title: {
            dataIndex: "name",
            search: false,
            render(dom, entity, index, action, schema) {
              return entity.course ? (
                <Text strong>{entity.course.title}</Text>
              ) : (
                entity.user?.nickName
              );
            },
          },
          description: {
            dataIndex: "desc",
            search: false,
            render(dom, entity, index, action, schema) {
              return (
                <Space>
                  <User {...entity.editor} showAvatar={false} />
                  <TimeShow type="secondary" updatedAt={entity.updated_at} />
                </Space>
              );
            },
          },
          subTitle: {
            search: false,
            render: (
              dom: React.ReactNode,
              entity: ICourseMemberData,
              index: number
            ) => {
              return (
                <Tag>
                  {intl.formatMessage({
                    id: `auth.role.${entity.role}`,
                  })}
                </Tag>
              );
            },
          },
          actions: {
            search: false,
            render: (text, row, index, action) => {
              const statusColor = getStatusColor(row.status);
              return [
                <span style={{ color: statusColor }}>
                  {intl.formatMessage({
                    id: `course.member.status.${row.status}.label`,
                  })}
                </span>,
              ];
            },
          },
          timeline: {
            // 自己扩展的字段，主要用于筛选，不在列表中显示
            title: "筛 选",
            valueType: "select",
            valueEnum: {
              all: { text: intl.formatMessage({ id: "course.timeline.all" }) },
              current: {
                text: intl.formatMessage({ id: "course.timeline.current" }),
              },
            },
          },
        }}
        request={async (params = {}, sorter, filter) => {
          console.info("filter", params, sorter, filter);

          let url = `/v2/course-member?view=timeline&course=${courseId}&userId=${userId}`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;
          if (typeof params.keyword !== "undefined") {
            url += "&search=" + (params.keyword ? params.keyword : "");
          }
          if (params.timeline) {
            url += `&timeline=${params.timeline}&request_course=1`;
          }
          console.info("api request", url);
          const res = await get<ICourseMemberListResponse>(url);
          if (res.ok) {
            console.debug("api response", res.data);
            return {
              total: res.data.count,
              succcess: true,
              data: res.data.rows,
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
        rowKey="id"
        bordered
        pagination={{
          showQuickJumper: true,
          showSizeChanger: true,
        }}
        options={{
          search: false,
        }}
      />
    </>
  );
};

export default CourseMemberTimeLineWidget;
