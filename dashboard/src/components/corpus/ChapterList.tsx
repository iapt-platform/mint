import { useState, useEffect } from "react";
import { List } from "antd";

import { get } from "../../request";
import { IChapterData, IChapterListResponse } from "../api/Corpus";
import ChapterCard from "./ChapterCard";
import type { ChapterData } from "./ChapterCard";
import type { ChannelFilterProps } from "../channel/ChannelList";

const defaultChannelFilterProps: ChannelFilterProps = {
  chapterProgress: 0.9,
  lang: "en",
  channelType: "translation",
};

interface IWidgetChannelList {
  filter?: ChannelFilterProps;
  tags?: string[];
}

const Widget = ({
  filter = defaultChannelFilterProps,
  tags = [],
}: IWidgetChannelList) => {
  const [tableData, setTableData] = useState<ChapterData[]>([]);

  useEffect(() => {
    console.log("useEffect");

    fetchData(filter, tags);
  }, [tags, filter]);

  function fetchData(filter: ChannelFilterProps, tags: string[]) {
    const strTags = tags.length > 0 ? "&tags=" + tags.join() : "";
    get<IChapterListResponse>(`/v2/progress?view=chapter${strTags}`).then(
      (json) => {
        console.log("chapter list ajax", json);
        let newTree: ChapterData[] = json.data.rows.map(
          (item: IChapterData) => {
            return {
              title: item.title,
              paliTitle: item.toc,
              path: item.path,
              book: item.book,
              paragraph: item.para,
              summary: item.summary,
              tag: item.tags,
              channel: {
                channelName: item.channel.name,
                channelId: item.channel_id,
                channelType: "translation",
                studioName: item.channel.name,
                studioId: item.channel.owner_uid,
                studioType: "",
              },
              createdAt: item.created_at,
              updatedAt: item.updated_at,
              hit: item.view,
              like: item.like,
              channelInfo: "string",
            };
          }
        );
        setTableData(newTree);
      }
    );
  }

  return (
    <List
      itemLayout="vertical"
      size="large"
      dataSource={tableData}
      renderItem={(item) => (
        <List.Item>
          <ChapterCard data={item} />
        </List.Item>
      )}
    />
  );
};

export default Widget;
