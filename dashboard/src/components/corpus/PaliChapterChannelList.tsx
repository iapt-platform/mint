import { useState, useEffect } from "react";

import { get } from "../../request";
import { IChapterChannelListResponse } from "../api/Corpus";
import { IChapter } from "./BookViewer";
import ChapterInChannel, { IChapterChannelData } from "./ChapterInChannel";

interface IWidget {
  para: IChapter;
  channelId?: string[];
  openTarget?: React.HTMLAttributeAnchorTarget;
}

const PaliChapterchannelListWidget = ({
  para,
  channelId,
  openTarget = "_blank",
}: IWidget) => {
  const [tableData, setTableData] = useState<IChapterChannelData[]>([]);

  useEffect(() => {
    let url = `/v2/progress?view=chapter_channels&book=${para.book}&par=${para.para}`;
    get<IChapterChannelListResponse>(url).then(function (json) {
      const newData: IChapterChannelData[] = json.data.rows.map((item) => {
        return {
          channel: {
            name: item.channel.name,
            id: item.channel.uid,
            type: item.channel.type,
          },
          studio: item.studio,
          progress: Math.ceil(item.progress * 100),
          progressLine: item.progress_line,
          hit: item.views,
          like: 0,
          updatedAt: item.updated_at,
        };
      });
      setTableData(newData);
      console.log("chapter", newData);
    });
  }, [para]);

  if (tableData.length > 0) {
    return (
      <ChapterInChannel
        data={tableData}
        book={para.book}
        para={para.para}
        channelId={channelId}
        openTarget={openTarget}
      />
    );
  } else {
    return <></>;
  }
};

export default PaliChapterchannelListWidget;
