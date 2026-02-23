import { List } from "antd";

import PaliChapterCard, { type IPaliChapterData } from "./PaliChapterCard";

export interface IChapterClickEvent {
  para: IPaliChapterData;
  event: React.MouseEvent<HTMLElement, MouseEvent>;
}

interface IWidgetPaliChapterList {
  data: IPaliChapterData[];
  maxLevel?: number;
  onChapterClick?: (v: IChapterClickEvent) => void;
}
const PaliChapterListWidget = ({
  data,
  maxLevel = 8,
  onChapterClick,
}: IWidgetPaliChapterList) => {
  return (
    <List
      itemLayout="vertical"
      size="large"
      dataSource={data}
      renderItem={(item) =>
        item.level <= maxLevel ? (
          <List.Item>
            <PaliChapterCard
              onTitleClick={(e) => {
                if (typeof onChapterClick !== "undefined") {
                  onChapterClick({
                    para: item,
                    event: e,
                  });
                }
              }}
              data={item}
            />
          </List.Item>
        ) : (
          <></>
        )
      }
    />
  );
};

export default PaliChapterListWidget;
