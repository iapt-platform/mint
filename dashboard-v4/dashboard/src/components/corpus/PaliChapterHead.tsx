import { useState, useEffect } from "react";
import { message } from "antd";

import { IApiResponsePaliChapter } from "../api/Corpus";
import { get } from "../../request";
import ChapterHead, { IChapterInfo } from "./ChapterHead";
import { IChapter } from "./BookViewer";
import TocPath, { ITocPathNode } from "./TocPath";

interface IWidget {
  para: IChapter;
  onChange?: Function;
}

const PaliChapterHeadWidget = ({ para, onChange }: IWidget) => {
  const [pathData, setPathData] = useState<ITocPathNode[]>([]);
  const [chapterData, setChapterData] = useState<IChapterInfo>({ title: "" });
  useEffect(() => {
    console.log("pali chapter list useEffect");
    fetchData(para);
  }, [para]);

  function fetchData(para: IChapter) {
    let url = `/v2/palitext?view=paragraph&book=${para.book}&para=${para.para}`;
    get<IApiResponsePaliChapter>(url).then(function (myJson) {
      console.log("ajax", myJson);
      const data = myJson;
      let path: ITocPathNode[] = JSON.parse(data.data.path);
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
  return (
    <>
      <TocPath
        data={pathData}
        onChange={(
          node: ITocPathNode,
          e: React.MouseEvent<HTMLSpanElement | HTMLAnchorElement, MouseEvent>
        ) => {
          message.success(node.book + ":" + node.paragraph);
          if (node.book && node.paragraph) {
            const chapter = {
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
