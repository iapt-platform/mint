import { parseInt } from "lodash";
import { useState, useEffect } from "react";
import { get } from "../../request";
import { IPaliTocListResponse } from "../api/Corpus";
import { ListNodeData } from "../studio/EditableTree";
import TocTree from "./TocTree";

interface IWidget {
  book?: number;
  para?: number;
  channel?: string;
}
const Widget = ({ book, para, channel }: IWidget) => {
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
  return <TocTree treeData={tocList} />;
};

export default Widget;
