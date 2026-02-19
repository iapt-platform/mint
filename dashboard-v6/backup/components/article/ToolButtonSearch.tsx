import { SearchOutlined } from "@ant-design/icons";

import ToolButton from "./ToolButton";
import { Input, Tree, type TreeDataNode } from "antd";
import { useSearchParams } from "react-router";
import type { ArticleType } from "./Article";
import { get } from "../../request";
import type { IArticleFtsListResponse } from "../../api/Article";
import type { Key } from "antd/lib/table/interface";
import { useState } from "react";

const { Search } = Input;

interface IWidget {
  type?: ArticleType;
  articleId?: string;
  anthologyId?: string;
  channels?: string[];
}
const ToolButtonSearchWidget = ({
  type,
  articleId,
  anthologyId,
  channels,
}: IWidget) => {
  const [_searchParams, _setSearchParams] = useSearchParams();
  const [treeNode, _setTreeNode] = useState<TreeDataNode[]>();
  const content = (
    <>
      <Search
        placeholder="搜索本章节"
        onSearch={(
          value: string,
          _event?:
            | React.ChangeEvent<HTMLInputElement>
            | React.MouseEvent<HTMLElement, MouseEvent>
            | React.KeyboardEvent<HTMLInputElement>
            | undefined
        ) => {
          if (type === "article") {
            let url = `/v2/article-fts?id=${articleId}&anthology=${anthologyId}&key=${value}`;
            url += "&channel=" + channels?.join(",");
            console.debug("api request", url);
            get<IArticleFtsListResponse>(url).then((json) => {
              console.debug("api response", json);
              if (json.ok) {
              }
            });
          }
        }}
        style={{ width: "100%" }}
      />
      <Tree onSelect={(_selectedKeys: Key[]) => {}} treeData={treeNode} />
    </>
  );

  return (
    <ToolButton title="搜索" icon={<SearchOutlined />} content={content} />
  );
};

export default ToolButtonSearchWidget;
