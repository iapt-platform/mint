import { useState, useEffect } from "react";

import PaliChapterChannelList from "./PaliChapterChannelList";
import PaliChapterListByPara from "./PaliChapterListByPara";
import PaliChapterHead from "./PaliChapterHead";
import type { IChapterClickEvent } from "./PaliChapterList";
import { Button, Typography } from "antd";
import { useIntl } from "react-intl";
import { Link } from "react-router";

export interface IChapter {
  book: number;
  para: number;
  level?: number;
}

interface IWidget {
  chapter: IChapter;
  onChange?: (currChapter: IChapter) => void;
}
const BookViewerWidget = ({ chapter, onChange }: IWidget) => {
  const intl = useIntl();
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
      {/* 不带 channel 打开阅读页，只看巴利原文；与下面资源链接一样新标签页打开 */}
      <Link
        to={`/workspace/tipitaka/chapter/${currChapter.book}-${currChapter.para}`}
        target="_blank"
      >
        <Button type="primary">
          {intl.formatMessage({ id: "buttons.open.original" })}
        </Button>
      </Link>
      <div style={{ marginTop: 8 }}>
        <Typography.Title level={5}>
          {intl.formatMessage({ id: "labels.resources" })}
        </Typography.Title>
        <PaliChapterChannelList para={currChapter} />
      </div>
      <div style={{ marginTop: 16 }}>
        <Typography.Title level={5}>
          {intl.formatMessage({ id: "labels.table-of-content" })}
        </Typography.Title>
        <PaliChapterListByPara
          compact
          chapter={currChapter}
          onChapterClick={(e: IChapterClickEvent) => {
            setCurrChapter({ book: e.para.Book, para: e.para.Paragraph });
          }}
        />
      </div>
    </>
  );
};

export default BookViewerWidget;
