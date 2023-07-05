import { useIntl } from "react-intl";
import { Dropdown, Typography } from "antd";
import { SearchOutlined } from "@ant-design/icons";
import { ActionType, ProTable } from "@ant-design/pro-components";
import { get } from "../../../request";
import { ArticleMode, ArticleType } from "../../../components/article/Article";
import { useAppSelector } from "../../../hooks";
import { currentUser as _currentUser } from "../../../reducers/current-user";
import { useEffect, useRef, useState } from "react";
import ArticleDrawer from "../../../components/article/ArticleDrawer";
import { useNavigate } from "react-router-dom";

export interface IRecentRequest {
  type: ArticleType;
  article_id: string;
  param?: string;
}
interface IParam {
  book?: string;
  para?: string;
  channel?: string;
  mode?: string;
}
interface IRecentData {
  id: string;
  title: string;
  type: ArticleType;
  article_id: string;
  param: string | null;
  updated_at: string;
}

export interface IRecentResponse {
  ok: boolean;
  message: string;
  data: IRecentData;
}
interface IRecentListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IRecentData[];
    count: number;
  };
}

interface IRecent {
  id: string;
  title: string;
  type: ArticleType;
  articleId: string;
  updatedAt: string;
  param?: IParam;
}
interface IArticleParam {
  type: ArticleType;
  articleId: string;
  mode?: ArticleMode;
  channelId?: string;
  book?: string;
  para?: string;
}
const Widget = () => {
  const intl = useIntl();
  const user = useAppSelector(_currentUser);
  const ref = useRef<ActionType>();
  const [articleOpen, setArticleOpen] = useState(false);
  const [param, setParam] = useState<IArticleParam>();

  useEffect(() => {
    ref.current?.reload();
  }, [user]);
  return (
    <>
      <ProTable<IRecent>
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
                <Typography.Link
                  key={index}
                  onClick={(event) => {
                    if (event.ctrlKey) {
                      let url = `/article/${row.type}/${row.articleId}?mode=`;
                      url += row.param?.mode ? row.param?.mode : "read";
                      url += row.param?.channel
                        ? `&channel=${row.param?.channel}`
                        : "";
                      url += row.param?.book ? `&book=${row.param?.book}` : "";
                      url += row.param?.para ? `&par=${row.param?.para}` : "";
                      const fullUrl =
                        process.env.REACT_APP_WEB_HOST +
                        process.env.PUBLIC_URL +
                        url;
                      window.open(fullUrl, "_blank");
                    } else {
                      setParam({
                        type: row.type,
                        articleId: row.articleId,
                        mode: row.param?.mode as ArticleMode,
                        channelId: row.param?.channel,
                        book: row.param?.book,
                        para: row.param?.para,
                      });
                      setArticleOpen(true);
                    }
                  }}
                >
                  {row.title}
                </Typography.Link>
              );
            },
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.type.label",
            }),
            dataIndex: "type",
            key: "type",
            width: 100,
            search: false,
            filters: true,
            onFilter: true,
            valueEnum: {
              all: { text: "全部", status: "Default" },
              chapter: { text: "章节", status: "Success" },
              article: { text: "文章", status: "Success" },
              para: { text: "段落", status: "Success" },
              sent: { text: "句子", status: "Success" },
            },
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.updated-at.label",
            }),
            key: "updated-at",
            width: 100,
            search: false,
            dataIndex: "updatedAt",
            valueType: "date",
          },
          {
            title: intl.formatMessage({ id: "buttons.option" }),
            key: "option",
            width: 120,
            valueType: "option",
            render: (text, row, index, action) => [
              <Dropdown.Button
                type="link"
                key={index}
                trigger={["click", "contextMenu"]}
                menu={{
                  items: [
                    {
                      key: "open",
                      label: "在藏经阁中打开",
                      icon: <SearchOutlined />,
                    },
                    {
                      key: "share",
                      label: "分享",
                      icon: <SearchOutlined />,
                    },
                    {
                      key: "delete",
                      label: "删除",
                      icon: <SearchOutlined />,
                    },
                  ],
                  onClick: (e) => {
                    switch (e.key) {
                      case "share":
                        break;
                      case "delete":
                        break;
                      default:
                        break;
                    }
                  },
                }}
              >
                {intl.formatMessage({ id: "buttons.edit" })}
              </Dropdown.Button>,
            ],
          },
        ]}
        request={async (params = {}, sorter, filter) => {
          console.log(params, sorter, filter);
          if (typeof user === "undefined") {
            return {
              total: 0,
              succcess: false,
              data: [],
            };
          }
          let url = `/v2/recent?view=user&id=${user?.id}`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 10);
          url += `&limit=${params.pageSize}&offset=${offset}`;
          url += params.keyword ? "&search=" + params.keyword : "";
          console.log("url", url);
          const res = await get<IRecentListResponse>(url);
          console.log("article list", res);
          const items: IRecent[] = res.data.rows.map((item, id) => {
            return {
              sn: id + 1,
              id: item.id,
              title: item.title,
              type: item.type,
              articleId: item.article_id,
              param: item.param ? JSON.parse(item.param) : undefined,
              updatedAt: item.updated_at,
            };
          });
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
        }}
        search={false}
        options={{
          search: true,
        }}
      />
      <ArticleDrawer
        {...param}
        open={articleOpen}
        onClose={() => setArticleOpen(false)}
      />
    </>
  );
};

export default Widget;
