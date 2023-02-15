import { useState, useEffect } from "react";
import { List } from "antd";

import { get } from "../../request";
import { IChapterData, IChapterListResponse } from "../api/Corpus";
import ChapterCard from "./ChapterCard";
import type { ChapterData } from "./ChapterCard";
import type { ChannelFilterProps } from "../channel/ChannelList";

interface IWidget {
  filter?: ChannelFilterProps;
  progress?: number;
  lang?: string;
  type?: string;
  tags?: string[];
  onTagClick?: Function;
}

const Widget = ({
  progress = 0.9,
  lang = "zh",
  type = "translation",
  tags = [],
  onTagClick,
}: IWidget) => {
  const [tableData, setTableData] = useState<ChapterData[]>([]);
  const [total, setTotal] = useState<number>();
  const [currPage, setCurrPage] = useState<number>(1);
  useEffect(() => {
    fetchData(
      { chapterProgress: progress, lang: lang, channelType: type },
      tags,
      currPage
    );
  }, [progress, lang, type, tags, currPage]);

  function fetchData(filter: ChannelFilterProps, tags: string[], page = 1) {
    const strTags = tags.length > 0 ? "&tags=" + tags.join() : "";
    const offset = (page - 1) * 20;
    get<IChapterListResponse>(
      `/v2/progress?view=chapter${strTags}&offset=${offset}&progress=${filter.chapterProgress}&lang=${filter.lang}&channel_type=${filter.channelType}`
    ).then((json) => {
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
        setTotal(json.data.count);
        setTableData(newTree);
      } else {
        setTotal(0);
        setTableData([]);
      }
    });
  }

  return (
    <List
      itemLayout="vertical"
      size="small"
      dataSource={tableData}
      pagination={{
        onChange: (page) => {
          console.log(page);
          setCurrPage(page);
        },
        showQuickJumper: true,
        showSizeChanger: false,
        pageSize: 20,
        total: total,
        position: "both",
        showTotal: (total) => {
          return `结果: ${total}`;
        },
      }}
      renderItem={(item) => (
        <List.Item>
          <ChapterCard
            data={item}
            onTagClick={(tag: string) => {
              if (typeof onTagClick !== "undefined") {
                onTagClick(tag);
              }
            }}
          />
        </List.Item>
      )}
    />
  );
};

export default Widget;
