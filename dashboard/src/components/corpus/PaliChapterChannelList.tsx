import { useState, useEffect } from "react";

import { get } from "../../request";
import { IApiResponseChapterChannelList } from "../api/Corpus";
import { IChapter } from "./BookViewer";
import ChapterInChannel, { IChapterChannelData } from "./ChapterInChannel";

interface IWidgetPaliChapterChannelList {
  para: IChapter;
}
const defaultData: IChapterChannelData[] = [];
const Widget = ({ para }: IWidgetPaliChapterChannelList) => {
  const [tableData, setTableData] = useState(defaultData);

  useEffect(() => {
    console.log("palichapterlist useEffect");
    let url = `/v2/progress?view=chapter_channels&book=${para.book}&par=${para.para}`;
    get<IApiResponseChapterChannelList>(url).then(function (json) {
      const newData: IChapterChannelData[] = json.data.rows.map((item) => {
        return {
          channel: {
            name: item.channel.name,
            id: item.channel.uid,
            type: item.channel.type,
          },
          studio: item.studio,
          progress: Math.ceil(item.progress * 100),
          hit: item.views,
          like: 0,
          updatedAt: item.updated_at,
        };
      });
      setTableData(newData);
    });
  }, [para]);

  return (
    <>
      <ChapterInChannel data={tableData} book={para.book} para={para.para} />
    </>
  );
};

export default Widget;
