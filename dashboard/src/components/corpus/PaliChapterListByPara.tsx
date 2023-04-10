import { useState, useEffect } from "react";

import { get } from "../../request";
import { IApiResponsePaliChapterList } from "../api/Corpus";
import { IChapter } from "./BookViewer";
import { IPaliChapterData } from "./PaliChapterCard";
import PaliChapterList, { IChapterClickEvent } from "./PaliChapterList";

interface IWidget {
  chapter: IChapter;
  onChapterClick?: Function;
}
const Widget = ({ chapter, onChapterClick }: IWidget) => {
  const [tableData, setTableData] = useState<IPaliChapterData[]>([]);

  useEffect(() => {
    console.log("palichapterlist useEffect");
    let url = `/v2/palitext?view=chapter_children&book=${chapter.book}&para=${chapter.para}`;
    get<IApiResponsePaliChapterList>(url).then(function (json) {
      console.log("chapter ajex", json);
      const newTree: IPaliChapterData[] = json.data.rows.map((item) => {
        return {
          Title: item.toc,
          PaliTitle: item.toc,
          level: item.level,
          Path: item.path,
          Book: item.book,
          Paragraph: item.paragraph,
          progressLine: item.progress_line,
        };
      });
      setTableData(newTree);
    });
  }, [chapter]);

  return (
    <>
      <PaliChapterList
        onChapterClick={(e: IChapterClickEvent) => {
          if (onChapterClick) {
            onChapterClick(e);
          }
        }}
        data={tableData}
      />
    </>
  );
};

export default Widget;
