import { useState, useEffect } from "react";

import { get } from "../../request";
import { IPaliChapterListResponse } from "../api/Corpus";
import { IChapter } from "./BookViewer";
import { IPaliChapterData } from "./PaliChapterCard";
import PaliChapterList, { IChapterClickEvent } from "./PaliChapterList";

interface IWidget {
  chapter: IChapter;
  onChapterClick?: Function;
}
const PaliChapterListByParaWidget = ({ chapter, onChapterClick }: IWidget) => {
  const [tableData, setTableData] = useState<IPaliChapterData[]>([]);

  useEffect(() => {
    console.log("palichapterlist useEffect");
    let url = `/v2/palitext?view=chapter_children&book=${chapter.book}&para=${chapter.para}`;
    get<IPaliChapterListResponse>(url).then(function (json) {
      console.log("chapter ajex", json);
      const newTree: IPaliChapterData[] = json.data.rows.map((item) => {
        return {
          Title: item.toc,
          PaliTitle: item.toc,
          level: item.level,
          Path: item.path,
          Book: item.book,
          Paragraph: item.paragraph,
          chapterStrLen: item.chapter_strlen,
          paragraphCount: item.chapter_len,
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

export default PaliChapterListByParaWidget;
