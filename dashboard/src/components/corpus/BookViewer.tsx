import { useState, useEffect } from "react";

import PaliChapterChannelList from "./PaliChapterChannelList";
import PaliChapterListByPara from "./PaliChapterListByPara";
import PaliChapterHead from "./PaliChapterHead";
import { IChapterClickEvent } from "./PaliChapterList";

export interface IChapter {
  book: number;
  para: number;
}

interface IWidget {
  chapter: IChapter;
  onChange?: Function;
}
const Widget = ({ chapter, onChange }: IWidget) => {
  const [currChapter, setCurrChpater] = useState(chapter);
  useEffect(() => {
    if (typeof onChange !== "undefined") {
      onChange(currChapter);
    }
  }, [currChapter]);

  useEffect(() => {
    setCurrChpater(chapter);
  }, [chapter]);
  return (
    <>
      <PaliChapterHead
        onChange={(e: IChapter) => {
          setCurrChpater(e);
        }}
        para={currChapter}
      />
      <PaliChapterChannelList para={currChapter} />
      <PaliChapterListByPara
        chapter={currChapter}
        onChapterClick={(e: IChapterClickEvent) => {
          setCurrChpater({ book: e.para.Book, para: e.para.Paragraph });
          console.log("PaliChapterListByPara", "onchange", e);
        }}
      />
    </>
  );
};

export default Widget;
