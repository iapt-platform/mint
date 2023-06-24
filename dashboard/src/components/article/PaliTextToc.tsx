import { Key } from "antd/lib/table/interface";
import { useState, useEffect } from "react";

import { get } from "../../request";
import { IPaliTocListResponse } from "../api/Corpus";
import { ListNodeData } from "./EditableTree";
import TocTree from "./TocTree";

interface IWidget {
  book?: number;
  para?: number;
  channel?: string;
  onSelect?: Function;
}
const PaliTextTocWidget = ({ book, para, channel, onSelect }: IWidget) => {
  const [tocList, setTocList] = useState<ListNodeData[]>([]);
  useEffect(() => {
    get<IPaliTocListResponse>(
      `/v2/palitext?view=book-toc&book=${book}&para=${para}`
    ).then((json) => {
      const toc = json.data.rows.map((item, id) => {
        return {
          key: `${item.book}-${item.paragraph}`,
          title: item.toc,
          level: parseInt(item.level),
        };
      });
      setTocList(toc);
    });
  }, [book, para]);
  return (
    <TocTree
      treeData={tocList}
      expandedKey={[`${book}-${para}`]}
      onSelect={(selectedKeys: Key[]) => {
        if (typeof onSelect !== "undefined") {
          onSelect(selectedKeys);
        }
      }}
    />
  );
};

export default PaliTextTocWidget;
