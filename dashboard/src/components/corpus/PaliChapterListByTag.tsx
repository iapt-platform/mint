import { useState, useEffect } from "react";

import { get } from "../../request";
import { IPaliChapterListResponse } from "../api/Corpus";
import { IPaliChapterData } from "./PaliChapterCard";
import PaliChapterList, { IChapterClickEvent } from "./PaliChapterList";

interface IWidget {
  tag: string[];
  onChapterClick?: Function;
}

const PaliChapterListByTagWidget = ({ tag = [], onChapterClick }: IWidget) => {
  const [tableData, setTableData] = useState<IPaliChapterData[]>([]);

  useEffect(() => {
    if (tag.length === 0) {
      setTableData([]);
      return;
    }
    let url = `/v2/palitext?view=chapter&tags=${tag.join()}`;
    console.log("url", url);
    get<IPaliChapterListResponse>(url).then((json) => {
      if (json.ok) {
        let newTree: IPaliChapterData[] = json.data.rows.map((item) => {
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
      data={tableData}
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
