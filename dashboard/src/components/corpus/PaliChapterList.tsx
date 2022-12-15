import { List } from "antd";

import PaliChapterCard, { IPaliChapterData } from "./PaliChapterCard";

interface IWidgetPaliChapterList {
  data: IPaliChapterData[];
  onChapterClick?: Function;
}

export interface IChapterClickEvent {
  para: IPaliChapterData;
  event: React.MouseEvent<HTMLDivElement, MouseEvent>;
}
const Widget = (prop: IWidgetPaliChapterList) => {
  return (
    <List
      itemLayout="vertical"
      size="large"
      dataSource={prop.data}
      renderItem={(item) => (
        <List.Item>
          <PaliChapterCard
            onTitleClick={(e: React.MouseEvent<HTMLDivElement, MouseEvent>) => {
              if (typeof prop.onChapterClick !== "undefined") {
                prop.onChapterClick({
                  para: item,
                  event: e,
                });
              }
            }}
            data={item}
          />
        </List.Item>
      )}
    />
  );
};

export default Widget;
