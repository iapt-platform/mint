import { Badge, Card, List, Typography } from "antd";
import { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { get } from "../../request";
import { IChapterData, IChapterListResponse } from "../api/Corpus";
import StudioName from "../auth/Studio";
import { ChapterData } from "../corpus/ChapterCard";
const { Title, Text, Paragraph } = Typography;

const ChapterNewListWidget = () => {
  const [listData, setListData] = useState<ChapterData[]>([]);
  const navigate = useNavigate();
  useEffect(() => {
    get<IChapterListResponse>(`/v2/progress?view=chapter&limit=4&lang=zh`).then(
      (json) => {
        console.log("chapter list ajax", json);
        if (json.ok) {
          let newTree: ChapterData[] = json.data.rows.map(
            (item: IChapterData) => {
              return {
                title: item.title,
                paliTitle: item.toc,
                path: item.path,
                book: item.book,
                paragraph: item.para,
                summary: item.summary,
                tag: item.tags.map((item) => {
                  return { title: item.name, key: item.name };
                }),
                channel: {
                  name: item.channel.name,
                  id: item.channel_id,
                  type: "translation",
                },
                studio: item.studio,
                progress: Math.round(item.progress * 100),
                createdAt: item.created_at,
                updatedAt: item.updated_at,
                hit: item.view,
                like: item.like,
                channelInfo: "string",
              };
            }
          );
          setListData(newTree);
        }
      }
    );
  }, []);
  return (
    <List
      itemLayout="vertical"
      size="small"
      split={false}
      dataSource={listData}
      renderItem={(item) => {
        const channel = item.channel.id ? `?channel=${item.channel.id}` : "";
        return (
          <List.Item>
            <Badge.Ribbon
              text="首发"
              color="darkred"
              style={{ display: "none" }}
            >
              <Card
                hoverable
                bordered={false}
                style={{ width: "100%", borderRadius: 20 }}
                onClick={(e) => {
                  navigate(
                    `/article/chapter/${item.book}-${item.paragraph}${channel}`
                  );
                }}
              >
                <Title level={5}>
                  <Link
                    to={`/article/chapter/${item.book}-${item.paragraph}${channel}`}
                  >
                    {item.title ? item.title : item.paliTitle}
                  </Link>
                </Title>
                <div>
                  <Text type="secondary">{item.paliTitle}</Text>
                </div>
                <Paragraph
                  ellipsis={{
                    rows: 2,
                    expandable: false,
                    symbol: "more",
                  }}
                >
                  <Text>{item.summary}</Text>
                </Paragraph>
                <StudioName data={item.studio} />
              </Card>
            </Badge.Ribbon>
          </List.Item>
        );
      }}
    />
  );
};

export default ChapterNewListWidget;
