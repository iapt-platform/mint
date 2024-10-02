import { useState, useEffect } from "react";

import PaliChapterChannelList from "./PaliChapterChannelList";
import PaliChapterListByPara from "./PaliChapterListByPara";
import PaliChapterHead from "./PaliChapterHead";
import { IChapterClickEvent } from "./PaliChapterList";
import { Tabs } from "antd";

export interface IChapter {
  book: number;
  para: number;
  level?: number;
}

interface IWidget {
  chapter: IChapter;
  onChange?: Function;
}
const BookViewerWidget = ({ chapter, onChange }: IWidget) => {
  const [currChapter, setCurrChapter] = useState(chapter);
  useEffect(() => {
    if (typeof onChange !== "undefined") {
      onChange(currChapter);
    }
  }, [currChapter, onChange]);

  useEffect(() => {
    setCurrChapter(chapter);
  }, [chapter]);
  return (
    <>
      <PaliChapterHead
        onChange={(e: IChapter) => {
          setCurrChapter(e);
        }}
        para={currChapter}
      />
      <Tabs
        size="small"
        items={[
          {
            label: `目录`,
            key: "toc",
            children: (
              <PaliChapterListByPara
                chapter={currChapter}
                onChapterClick={(e: IChapterClickEvent) => {
                  setCurrChapter({ book: e.para.Book, para: e.para.Paragraph });
                  console.log("PaliChapterListByPara", "onchange", e);
                }}
              />
            ),
          },
          {
            label: `资源`,
            key: "res",
            children: <PaliChapterChannelList para={currChapter} />,
          },
        ]}
      />
    </>
  );
};

export default BookViewerWidget;
