import { useState, useEffect } from "react";
import { get } from "../../request";
import { IApiResponseChapterChannelList } from "../api/Corpus";
import { IParagraph } from "./BookViewer";
import ChapterInChannel, { IChapterChannelData } from "./ChapterInChannel";

interface IWidgetPaliChapterChannelList {
  para: IParagraph;
}
const defaultData: IChapterChannelData[] = [];
const Widget = ({ para }: IWidgetPaliChapterChannelList) => {
  const [tableData, setTableData] = useState(defaultData);

  useEffect(() => {
    console.log("palichapterlist useEffect");
    let url = `/v2/progress?view=chapter_channels&book=${para.book}&par=${para.para}`;
    get(url).then(function (myJson) {
      console.log("ajex", myJson);
      const data = myJson as unknown as IApiResponseChapterChannelList;
      const newData: IChapterChannelData[] = data.data.rows.map((item) => {
        return {
          channel: {
            channelName: item.channel.name,
            channelId: item.channel.uid,
            channelType: item.channel.type,
            studioName: "V",
            studioId: "123",
            studioType: "p",
          },
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
