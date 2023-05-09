import { useState, useEffect } from "react";

import { get } from "../../request";
import { IPaliChapterListResponse } from "../api/Corpus";
import { IPaliChapterData } from "./PaliChapterCard";
import PaliChapterList, { IChapterClickEvent } from "./PaliChapterList";

interface IWidgetPaliChapterListByTag {
  tag: string[];
  onChapterClick?: Function;
}

const PaliChapterListByTagWidget = (prop: IWidgetPaliChapterListByTag) => {
  const [tableData, setTableData] = useState<IPaliChapterData[]>([]);

  useEffect(() => {
    console.log("palichapterlist useEffect");
    let url = `/v2/palitext?view=chapter&tags=${prop.tag.join()}`;
    console.log("tag url", url);
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
            progressLine: item.progress_line,
          };
        });
        setTableData(newTree);
      } else {
        console.error(json.message);
      }
    });
  }, [prop.tag]);

  return (
    <>
      <PaliChapterList
        data={tableData}
        maxLevel={1}
        onChapterClick={(e: IChapterClickEvent) => {
          if (typeof prop.onChapterClick !== "undefined") {
            prop.onChapterClick(e);
          }
        }}
      />
    </>
  );
};

export default PaliChapterListByTagWidget;
