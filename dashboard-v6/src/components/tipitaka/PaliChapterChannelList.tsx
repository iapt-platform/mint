import { useState, useEffect } from "react";

import type { IChapter } from "./BookViewer";
import ChapterInChannel, { type IChapterChannelData } from "./ChapterInChannel";
import { fetchChapterChannels } from "../../api/tipitaka-chapter-channel";
import type { TChannelType } from "../../api/channel";

interface IWidget {
  para: IChapter;
  channelId?: string[];
  openTarget?: React.HTMLAttributeAnchorTarget;
}

const PaliChapterChannelList = ({
  para,
  channelId,
  openTarget = "_blank",
}: IWidget) => {
  const [tableData, setTableData] = useState<IChapterChannelData[]>([]);

  useEffect(() => {
    fetchChapterChannels(para.book, para.para).then((rows) => {
      setTableData(
        rows.map((item) => ({
          channel: {
            name: item.channel?.name ?? "",
            id: item.channel?.id ?? "",
            type: item.channel?.type as TChannelType | undefined,
          },
          studio: item.studio?.id ? { ...item.studio, id: item.studio.id } : undefined,
          progress: Math.ceil((item.progress ?? 0) * 100),
          progressLine: item.progress_line ?? undefined,
          hit: item.views ?? 0,
          updatedAt: item.updated_at ?? "",
        }))
      );
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

export default PaliChapterChannelList;
