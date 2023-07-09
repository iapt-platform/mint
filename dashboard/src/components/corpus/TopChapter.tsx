import { Typography } from "antd";
import { ProList } from "@ant-design/pro-components";

import { get } from "../../request";
import { IChapterListResponse } from "../../components/api/Corpus";
import { Link } from "react-router-dom";

const { Paragraph } = Typography;

interface IItem {
  sn: number;
  title: string;
  subTitle: string;
  summary: string;
  book: number;
  paragraph: number;
  path: string;
  channelId: string;
  progress: number;
  view: number;
  createdAt: number;
  updatedAt: number;
}
interface IWidget {
  studioName?: string;
}
const TopChapterWidget = ({ studioName }: IWidget) => {
  return (
    <ProList<IItem>
      metas={{
        title: {
          render: (dom, entity, index, action, schema) => {
            return (
              <Link
                to={`/article/chapter/${entity.book}-${entity.paragraph}?mode=read&channel=${entity.channelId}`}
              >
                {entity.title ? entity.title : entity.subTitle}
              </Link>
            );
          },
        },
        subTitle: {},
        description: { dataIndex: "path" },
        type: {},
        avatar: {},
        content: {
          render: (dom, entity, index, action, schema) => {
            return (
              <Paragraph
                ellipsis={{ rows: 2, expandable: false, symbol: "more" }}
              >
                {entity.summary}
              </Paragraph>
            );
          },
        },
        actions: {
          cardActionProps: "extra",
        },
      }}
      showActions="hover"
      grid={{ gutter: 16, column: 2, md: 1 }}
      request={async (params = {}, sorter, filter) => {
        // TODO
        console.log(params, sorter, filter);
        const offset = (params.current || 1 - 1) * (params.pageSize || 20);
        const res = await get<IChapterListResponse>(
          `/v2/progress?view=chapter&studio=${studioName}&progress=0.9&limit=4`
        );
        console.log(res.data.rows);
        const items: IItem[] = res.data.rows.map((item, id) => {
          const createdAt = new Date(item.created_at);
          const updatedAt = new Date(item.updated_at);
          return {
            sn: id + offset + 1,
            book: item.book,
            paragraph: item.para,
            view: item.view,
            title: item.title,
            subTitle: item.toc,
            summary: item.summary,
            channelId: item.channel_id,
            path: item.path,
            progress: item.progress,
            createdAt: createdAt.getTime(),
            updatedAt: updatedAt.getTime(),
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
      search={false}
    />
  );
};

export default TopChapterWidget;
