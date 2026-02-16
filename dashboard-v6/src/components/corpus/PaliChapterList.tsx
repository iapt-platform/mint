import { List } from "antd";

import PaliChapterCard, { IPaliChapterData } from "./PaliChapterCard";

export interface IChapterClickEvent {
  para: IPaliChapterData;
  event: React.MouseEvent<HTMLDivElement, MouseEvent>;
}

interface IWidgetPaliChapterList {
  data: IPaliChapterData[];
  maxLevel?: number;
  onChapterClick?: Function;
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
              onTitleClick={(
                e: React.MouseEvent<HTMLDivElement, MouseEvent>
              ) => {
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
