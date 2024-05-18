import { useState, useEffect } from "react";

import { get } from "../../request";
import type { ChannelFilterProps } from "../channel/ChannelList";
import { ITagData } from "./ChapterTag";
import TagArea from "../tag/TagAreaInChapter";
import { Skeleton } from "antd";

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
  max?: number;
  onTagClick?: Function;
}

const ChapterTagListWidget = ({
  progress = 0.9,
  lang = "zh",
  type = "translation",
  tags = [],
  max,
  onTagClick,
}: IWidget) => {
  const [tag, setTag] = useState<ITagData[]>([]);
  const [load, setLoad] = useState(true);

  useEffect(() => {
    const strTags = tags.length > 0 ? "&tags=" + tags.join() : "";
    const url = `/v2/tags-in-chapter?view=chapter${strTags}&progress=${progress}&lang=${lang}&channel_type=${type}`;
    console.log("tag list ajax", url);
    setLoad(true);
    get<IChapterTagResponse>(url)
      .then((json) => {
        if (json.ok) {
          if (json.data.count === 0) {
            setTag([]);
          } else {
            const max = json.data.rows.sort((a, b) => b.count - a.count)[0]
              .count;
            const data: ITagData[] = json.data.rows
              .filter((value) => value.count < max)
              .map((item) => {
                return {
                  key: item.name,
                  title: item.name,
                  count: item.count,
                };
              });
            setTag(data);
          }
        } else {
          setTag([]);
        }
      })
      .finally(() => setLoad(false));
  }, [progress, lang, type, tags]);

  return (
    <div>
      {load ? (
        <Skeleton paragraph={{ rows: 4 }} active />
      ) : tag.length === 0 ? (
        "无"
      ) : (
        <TagArea
          max={max}
          data={tag}
          onTagClick={(tag: string) => {
            if (typeof onTagClick !== "undefined") {
              onTagClick(tag);
            }
          }}
        />
      )}
    </div>
  );
};

export default ChapterTagListWidget;
