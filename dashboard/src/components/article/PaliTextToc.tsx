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
  const [selectedKeys, setSelectedKeys] = useState<Key[]>();
  const [expandedKeys, setExpandedKeys] = useState<Key[]>();

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
      if (json.data.rows.length > 0) {
        let path: string[] = [];
        for (let index = json.data.rows.length - 1; index >= 0; index--) {
          const element = json.data.rows[index];
          if (element.book === book && para && element.paragraph <= para) {
            path.push(`${element.book}-${element.paragraph}`);
            break;
          }
        }
        setExpandedKeys(path);
        setSelectedKeys(path);
      }
    });
  }, [book, para]);

  return (
    <TocTree
      randomKey={false}
      treeData={tocList}
      selectedKeys={selectedKeys}
      expandedKeys={expandedKeys}
      onSelect={(selectedKeys: Key[]) => {
        if (typeof onSelect !== "undefined") {
          onSelect(selectedKeys);
        }
      }}
    />
  );
};

export default PaliTextTocWidget;
