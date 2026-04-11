import type { Key } from "antd/lib/table/interface";
import { Skeleton } from "antd";

import { useChapterToc } from "./hooks/useChapterToc";
import type { IChapterToc } from "../../api/pali-text";
import type { ListNodeData } from "./components/EditableTree";
import TocTree from "./components/TocTree";

interface IWidget {
  book: number;
  para: number;
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
  const { data, loading } = useChapterToc({ book, para });

  const chapters = data.rows.filter((item) => item.level <= maxLevel);

  // onData 回调：chapters 变化时通知父组件
  // 放在 useMemo/useEffect 取决于父组件是否需要在渲染外消费
  // 这里跟原逻辑保持一致，在渲染时调用
  onData?.(chapters);

  const tocList: ListNodeData[] = chapters.map((item) => ({
    key: `${item.book}-${item.paragraph}`,
    title: item.text,
    level: item.level,
  }));

  if (loading) return <Skeleton active />;

  return (
    <TocTree
      treeData={tocList}
      onSelect={(selectedKeys: Key[]) => onSelect?.(selectedKeys)}
    />
  );
};

export default ChapterTocWidget;
