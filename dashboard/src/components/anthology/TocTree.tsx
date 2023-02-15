import { Key } from "antd/lib/table/interface";
import { useEffect, useState } from "react";

import { get } from "../../request";
import { IArticleMapListResponse } from "../api/Article";
import EditableTree, { ListNodeData } from "../article/EditableTree";

interface IWidget {
  anthologyId?: string;
  onSelect?: Function;
}
const Widget = ({ anthologyId, onSelect }: IWidget) => {
  const [tocData, setTocData] = useState<ListNodeData[]>([]);
  const [keys, setKeys] = useState<Key[]>();

  useEffect(() => {
    get<IArticleMapListResponse>(
      `/v2/article-map?view=anthology&id=${anthologyId}`
    ).then((json) => {
      console.log("文集get", json);
      if (json.ok) {
        const toc: ListNodeData[] = json.data.rows.map((item) => {
          return {
            key: item.id ? item.id : item.title,
            title: item.title,
            level: item.level,
          };
        });
        setTocData(toc);
      }
    });
  }, [anthologyId]);
  return (
    <div>
      <EditableTree
        treeData={tocData}
        onChange={(data: ListNodeData[]) => {
          setTocData(data);
        }}
        onSelect={(selectedKeys: Key[]) => {
          setKeys(selectedKeys);
        }}
      />
    </div>
  );
};

export default Widget;
