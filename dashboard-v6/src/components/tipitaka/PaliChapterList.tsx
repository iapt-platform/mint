import { List } from "antd";

import PaliChapterCard, { type IPaliChapterData } from "./PaliChapterCard";
import styles from "./PaliChapterList.module.css";

export interface IChapterClickEvent {
  para: IPaliChapterData;
  event: React.MouseEvent<HTMLElement, MouseEvent>;
}

interface IWidgetPaliChapterList {
  data: IPaliChapterData[];
  maxLevel?: number;
  /** 紧凑模式：小号列表 + 单行卡片（右侧抽屉里用） */
  compact?: boolean;
  onChapterClick?: (v: IChapterClickEvent) => void;
}
const PaliChapterListWidget = ({
  data,
  maxLevel = 8,
  compact = false,
  onChapterClick,
}: IWidgetPaliChapterList) => {
  const emitClick = (
    item: IPaliChapterData,
    e: React.MouseEvent<HTMLElement, MouseEvent>
  ) => {
    if (typeof onChapterClick !== "undefined") {
      onChapterClick({ para: item, event: e });
    }
  };

  return (
    <List
      itemLayout="vertical"
      size={compact ? "small" : "large"}
      dataSource={data}
      renderItem={(item) =>
        item.level <= maxLevel ? (
          compact ? (
            // 紧凑模式整行可点，点击交给 List.Item，卡片里的标题不再单独绑定
            <List.Item
              className={styles.compactItem}
              onClick={(e) => emitClick(item, e)}
            >
              <PaliChapterCard compact data={item} />
            </List.Item>
          ) : (
            <List.Item>
              <PaliChapterCard
                onTitleClick={(e) => emitClick(item, e)}
                data={item}
              />
            </List.Item>
          )
        ) : (
          <></>
        )
      }
    />
  );
};

export default PaliChapterListWidget;
