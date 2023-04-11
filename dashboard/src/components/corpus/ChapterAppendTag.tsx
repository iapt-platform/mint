import { useState, useEffect } from "react";
import { Button, Popover } from "antd";

import { get } from "../../request";
import type { ChannelFilterProps } from "../channel/ChannelList";
import { ITagData } from "./ChapterTag";
import TagArea from "../tag/TagArea";

interface IAppendTagData {
  id: string;
  name: string;
  count: number;
}
interface IChapterTagResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IAppendTagData[];
    count: number;
  };
}

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
  const [tag, setTag] = useState<ITagData[]>([]);

  useEffect(() => {
    fetchData(
      { chapterProgress: progress, lang: lang, channelType: type },
      tags
    );
  }, [progress, lang, type, tags]);

  function fetchData(filter: ChannelFilterProps, tags: string[]) {
    const strTags = tags.length > 0 ? "&tags=" + tags.join() : "";
    get<IChapterTagResponse>(
      `/v2/progress?view=chapter-tag${strTags}&progress=${filter.chapterProgress}&lang=${filter.lang}&channel_type=${filter.channelType}`
    ).then((json) => {
      console.log("chapter list ajax", json);
      if (json.ok) {
        if (json.data.count === 0) {
          setTag([]);
        } else {
          let count = json.data.rows[0].count;
          let isSame = true;
          for (const it of json.data.rows) {
            if (it.count !== count) {
              isSame = false;
              break;
            }
          }
          if (isSame) {
            setTag([]);
          } else {
            const data: ITagData[] = json.data.rows.map((item) => {
              return {
                key: item.name,
                title: item.name,
                count: item.count,
              };
            });
            setTag(data);
          }
        }
      } else {
        setTag([]);
      }
    });
  }

  return (
    <Popover
      content={
        <div style={{ width: 600 }}>
          {tag.length === 0 ? (
            "无"
          ) : (
            <TagArea
              data={tag}
              onTagClick={(tag: string) => {
                if (typeof onTagClick !== "undefined") {
                  onTagClick(tag);
                }
              }}
            />
          )}
        </div>
      }
      placement="bottom"
      trigger="hover"
    >
      <Button type="dashed">+</Button>
    </Popover>
  );
};

export default Widget;
