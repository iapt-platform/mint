import { Button, Space } from "antd";
import { useState } from "react";
import { ISentHistoryData } from "./SentHistory";
import SentHistoryItem from "./SentHistoryItem";

interface IWidget {
  data?: ISentHistoryData[];
  oldContent?: string;
}
const SentHistoryGroupWidget = ({ data = [], oldContent }: IWidget) => {
  const [compact, setCompact] = useState(true);
  return (
    <>
      {data.length > 0 ? (
        <div>
          {data.length > 1 ? (
            <Button type="link" onClick={() => setCompact(!compact)}>
              {compact ? `显示全部修改记录-${data.length}` : "折叠"}
            </Button>
          ) : undefined}
          {compact ? (
            <SentHistoryItem
              data={data[data.length - 1]}
              oldContent={oldContent}
            />
          ) : (
            <div>
              {data.map((item, index) => {
                return (
                  <SentHistoryItem
                    key={index}
                    data={item}
                    oldContent={
                      index === 0 ? oldContent : data[index - 1].content
                    }
                  />
                );
              })}
            </div>
          )}
        </div>
      ) : undefined}
    </>
  );
};

export default SentHistoryGroupWidget;
