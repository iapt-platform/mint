import { useState, useEffect } from "react";

import { get } from "../../request";
import type { IPaliChapterListResponse } from "../../api/pali-text";
import type { IPaliChapterData } from "./PaliChapterCard";
import PaliChapterList, { type IChapterClickEvent } from "./PaliChapterList";

interface IWidget {
  tag: string[];
  onChapterClick?: (e: IChapterClickEvent) => void;
}

const PaliChapterListByTagWidget = ({ tag = [], onChapterClick }: IWidget) => {
  const [tableData, setTableData] = useState<IPaliChapterData[]>([]);

  let mTableData: IPaliChapterData[] = [];
  if (tag.length === 0) {
    mTableData = [];
  } else {
    mTableData = tableData;
  }
  useEffect(() => {
    if (tag.length === 0) {
      return;
    }
    const url = `/api/v2/palitext?view=chapter&tags=${tag.join()}`;
    console.log("url", url);
    get<IPaliChapterListResponse>(url).then((json) => {
      if (json.ok) {
        const newTree: IPaliChapterData[] = json.data.rows.map((item) => {
          return {
            Title: item.title,
            PaliTitle: item.title,
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
      } else {
        console.error(json.message);
      }
    });
  }, [tag]);

  return (
    <PaliChapterList
      data={mTableData}
      maxLevel={1}
      onChapterClick={(e: IChapterClickEvent) => {
        if (typeof onChapterClick !== "undefined") {
          onChapterClick(e);
        }
      }}
    />
  );
};

export default PaliChapterListByTagWidget;
