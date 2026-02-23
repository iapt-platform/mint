import { useState, useEffect } from "react";
import { message } from "antd";

import type { IApiResponsePaliChapter, ITocPathNode } from "../../api/Corpus";
import { get } from "../../request";

import type { IChapter } from "./BookViewer";
import TocPath from "./TocPath";
import ChapterHead, { type IChapterInfo } from "./ChapterHead";

interface IOnChangeData {
  book: number;
  para: number;
  level: number;
}
interface IWidget {
  para: IChapter;
  onChange?: (chapter: IOnChangeData) => void;
}

const PaliChapterHeadWidget = ({ para, onChange }: IWidget) => {
  const [pathData, setPathData] = useState<ITocPathNode[]>([]);
  const [chapterData, setChapterData] = useState<IChapterInfo>({ title: "" });

  function fetchData(para: IChapter) {
    const url = `/api/v2/palitext?view=paragraph&book=${para.book}&para=${para.para}`;
    get<IApiResponsePaliChapter>(url).then(function (myJson) {
      console.log("ajax", myJson);
      const data = myJson;
      const path: ITocPathNode[] = JSON.parse(data.data.path);
      path.push({
        book: data.data.book,
        paragraph: data.data.paragraph,
        title: data.data.toc,
        paliTitle: data.data.toc,
        level: data.data.level,
      });
      setPathData(path);
      const chapter: IChapterInfo = {
        title: data.data.toc,
        subTitle: data.data.toc,
        book: data.data.book,
        para: data.data.paragraph,
      };
      setChapterData(chapter);
    });
  }
  useEffect(() => {
    console.log("pali chapter list useEffect");
    fetchData(para);
  }, [para]);

  return (
    <>
      <TocPath
        data={pathData}
        onChange={(node: ITocPathNode) => {
          message.success(node.book + ":" + node.paragraph);
          if (node.book && node.paragraph) {
            const chapter: IOnChangeData = {
              book: node.book,
              para: node.paragraph,
              level: node.level,
            };
            fetchData(chapter);
            if (typeof onChange !== "undefined") {
              onChange(chapter);
            }
          }
        }}
        link={"none"}
      />
      <ChapterHead data={chapterData} />
    </>
  );
};

export default PaliChapterHeadWidget;
