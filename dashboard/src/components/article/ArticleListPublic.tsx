import { Link } from "react-router-dom";
import { useRef } from "react";
import { Space } from "antd";
import { ActionType, ProList } from "@ant-design/pro-components";

import { get } from "../../request";
import { IArticleListResponse } from "../api/Article";

import { IStudio } from "../auth/Studio";
import { IUser } from "../auth/User";
import TimeShow from "../general/TimeShow";

interface DataItem {
  sn: number;
  id: string;
  title: string;
  subtitle: string;
  summary?: string | null;
  anthologyCount?: number;
  anthologyTitle?: string;
  publicity: number;
  createdAt?: string;
  updatedAt: string;
  studio?: IStudio;
  editor?: IUser;
}

interface IWidget {
  search?: string;
  studioName?: string;
}
const ArticleListWidget = ({ search, studioName }: IWidget) => {
  const ref = useRef<ActionType>();

  return (
    <>
      <ProList<DataItem>
        rowKey="id"
        actionRef={ref}
        metas={{
          title: {
            render: (text, row, index, action) => {
              return <Link to={`/article/article/${row.id}`}>{row.title}</Link>;
            },
          },
          description: {
            dataIndex: "summary",
          },
          subTitle: {
            render: (text, row, index, action) => {
              return (
                <Space>
                  {row.editor?.nickName}
                  <TimeShow
                    updatedAt={row.updatedAt}
                    showLabel={false}
                    showIcon={false}
                  />
                </Space>
              );
            },
          },
        }}
        request={async (params = {}, sorter, filter) => {
          let url = `/v2/article?view=public`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;
          url += params.keyword ? "&search=" + params.keyword : "";
          url += studioName ? "&studio=" + studioName : "";
          const res = await get<IArticleListResponse>(url);
          const items: DataItem[] = res.data.rows.map((item, id) => {
            return {
              sn: id + 1,
              id: item.uid,
              title: item.title,
              subtitle: item.subtitle,
              summary: item.summary,
              anthologyCount: item.anthology_count,
              anthologyTitle: item.anthology_first?.title,
              publicity: item.status,
              updatedAt: item.updated_at,
              studio: item.studio,
              editor: item.editor,
            };
          });
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
          pageSize: 20,
        }}
        search={false}
        options={{
          search: true,
        }}
      />
    </>
  );
};

export default ArticleListWidget;
