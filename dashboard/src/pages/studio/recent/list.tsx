import { useParams } from "react-router-dom";
import { useIntl } from "react-intl";
import { Link } from "react-router-dom";
import { Space, Table } from "antd";
import { Button, Dropdown } from "antd";
import { SearchOutlined } from "@ant-design/icons";
import { ProTable } from "@ant-design/pro-components";
import { get } from "../../../request";
import { json } from "stream/consumers";

interface IMetaChapter {
  book: number;
  para: number;
  channel: string;
  mode: string;
}
interface IViewData {
  id: string;
  target_id: string;
  target_type: string;
  updated_at: string;
  title: string;
  org_title: string;
  meta: string;
}
export interface IArticleResponse {
  ok: boolean;
  message: string;
  data: IViewData;
}
export interface IViewListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IViewData[];
    count: number;
  };
}

interface IView {
  id: string;
  title: string;
  subtitle: string;
  type: string;
  updatedAt: string;
  meta: IMetaChapter;
}

const Widget = () => {
  const intl = useIntl();
  const { studioname } = useParams();
  return (
    <ProTable<IView>
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
                  <Link
                    to={`/article/chapter/${row.meta.book}-${row.meta.para}`}
                    target="_blank"
                  >
                    {row.title ? row.title : row.subtitle}
                  </Link>
                </div>
                <div>{row.title ? row.subtitle : undefined}</div>
              </div>
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
        let url = `/v2/view?view=studio&name=${studioname}`;
        const offset =
          ((params.current ? params.current : 1) - 1) *
          (params.pageSize ? params.pageSize : 10);
        url += `&limit=${params.pageSize}&offset=${offset}`;
        url += params.keyword ? "&search=" + params.keyword : "";

        const res = await get<IViewListResponse>(url);
        console.log("article list", res);
        const items: IView[] = res.data.rows.map((item, id) => {
          return {
            sn: id + 1,
            id: item.id,
            title: item.title,
            subtitle: item.org_title,
            type: item.target_type,
            meta: JSON.parse(item.meta),
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
  );
};

export default Widget;
