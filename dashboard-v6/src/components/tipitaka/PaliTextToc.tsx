// src/components/article/PaliTextToc.tsx
import { Skeleton } from "antd";

import TocTree from "./TocTree";
import { usePaliBookToc } from "./hooks/usePaliBookToc";

interface IWidget {
  book?: number;
  para?: number;
  series?: string;
  onSelect?: (selectedKeys?: string[]) => void;
  onClick?: (
    id: string,
    e: React.MouseEvent<HTMLSpanElement, MouseEvent>
  ) => void;
}

const PaliTextToc = ({ book, para, series, onSelect, onClick }: IWidget) => {
  const { tocList, selectedKeys, expandedKeys, loading } = usePaliBookToc({
    book,
    para,
    series,
  });

  if (loading) return <Skeleton active />;

  return (
    <TocTree
      treeData={tocList}
      selectedKeys={selectedKeys}
      expandedKeys={expandedKeys}
      onSelect={onSelect}
      onClick={onClick}
    />
  );
};

export default PaliTextToc;
