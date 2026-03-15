import { useState, useEffect } from "react";

import { get } from "../../request";

import { Skeleton } from "antd";
import type { Key } from "antd/lib/table/interface";
import type { IChapterToc, IChapterTocListResponse } from "../../api/pali-text";
import type { ListNodeData } from "./components/EditableTree";
import TocTree from "./components/TocTree";

interface IWidget {
  book?: number;
  para?: number;
  maxLevel?: number;
  onSelect?: (selectedKeys: Key[]) => void;
  onData?: (data: IChapterToc[]) => void;
}
const ChapterTocWidget = ({
  book,
  para,
  maxLevel = 8,
  onSelect,
  onData,
}: IWidget) => {
  const [tocList, setTocList] = useState<ListNodeData[]>([]);
  const [loading, setLoading] = useState(true);
  useEffect(() => {
    const url = `/v2/chapter?view=toc&book=${book}&para=${para}`;
    setLoading(true);
    console.info("api request", url);
    get<IChapterTocListResponse>(url)
      .then((json) => {
        console.info("api response", json);
        const chapters = json.data.rows.filter(
          (value) => value.level <= maxLevel
        );
        onData?.(chapters);
        const toc = chapters.map((item, id) => {
          return {
            key: `${item.book}-${item.paragraph}`,
            title: item.text,
            level: item.level,
          };
        });
        setTocList(toc);
        if (chapters.length > 0) {
          const path: string[] = [];
          for (let index = chapters.length - 1; index >= 0; index--) {
            const element = chapters[index];
            if (element.book === book && para && element.paragraph <= para) {
              path.push(`${element.book}-${element.paragraph}`);
              break;
            }
          }
        }
      })
      .finally(() => setLoading(false));
  }, [book, maxLevel, para]);

  return loading ? (
    <Skeleton active />
  ) : (
    <TocTree
      treeData={tocList}
      onSelect={(selectedKeys: Key[]) => {
        onSelect?.(selectedKeys);
      }}
    />
  );
};

export default ChapterTocWidget;
