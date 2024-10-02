import { Key } from "antd/lib/table/interface";
import { useState, useEffect } from "react";

import { get } from "../../request";
import { IPaliTocListResponse } from "../api/Corpus";
import { ListNodeData } from "./EditableTree";
import TocTree from "./TocTree";
import { Skeleton } from "antd";

interface IWidget {
  book?: number;
  para?: number;
  series?: string;
  channel?: string;
  onSelect?: Function;
  onClick?: Function;
}
const PaliTextTocWidget = ({
  book,
  para,
  series,
  channel,
  onSelect,
  onClick,
}: IWidget) => {
  const [tocList, setTocList] = useState<ListNodeData[]>([]);
  const [selectedKeys, setSelectedKeys] = useState<Key[]>();
  const [expandedKeys, setExpandedKeys] = useState<Key[]>();
  const [loading, setLoading] = useState(true);
  useEffect(() => {
    let url = `/v2/palitext?view=book-toc&book=${book}&para=${para}`;
    if (series) {
      url = `/v2/palitext?view=book-toc&series=${series}`;
    } else {
      url = `/v2/palitext?view=book-toc&book=${book}&para=${para}`;
    }
    setLoading(true);
    get<IPaliTocListResponse>(url)
      .then((json) => {
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
      })
      .finally(() => setLoading(false));
  }, [book, para, series]);

  return loading ? (
    <Skeleton active />
  ) : (
    <TocTree
      treeData={tocList}
      selectedKeys={selectedKeys}
      expandedKeys={expandedKeys}
      onSelect={(selectedKeys: Key[]) => {
        if (typeof onSelect !== "undefined") {
          onSelect(selectedKeys);
        }
      }}
      onClick={(
        id: string,
        e: React.MouseEvent<HTMLSpanElement, MouseEvent>
      ) => {
        if (typeof onClick !== "undefined") {
          onClick(id, e);
        }
      }}
    />
  );
};

export default PaliTextTocWidget;
