import { useState, useEffect } from "react";
import { List } from "antd";
import ChapterCard from "./ChapterCard";
import type { ChapterData } from "./ChapterCard";
import type { ChannelFilterProps } from "../channel/ChannelList";
import { IChapterData, IChapterListResponse } from "../api/Corpus";
import { get } from "../../request";

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
        console.log("ajax", json);
        let newTree = json.data.rows.map((item: IChapterData) => {
          return {
            Title: item.title,
            PaliTitle: item.toc,
            Path: item.path,
            Book: item.book,
            Paragraph: item.para,
            Summary: item.summary,
            Tag: item.tags,
            Channel: {
              channelName: item.channel.name,
              channelId: "",
              channelType: "translation",
              studioName: item.channel.name,
              studioId: item.channel.owner_uid,
              studioType: "",
            },
            CreatedAt: item.created_at,
            UpdatedAt: item.updated_at,
            Hit: item.view,
            Like: item.like,
            ChannelInfo: "string",
          };
        });
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
