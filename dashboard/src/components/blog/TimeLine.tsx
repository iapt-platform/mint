import { Timeline } from "antd";

interface IAuthorTimeLine {
  label: string;
  content: string;
  type: string;
}
const TimeLineWidget = () => {
  const data: IAuthorTimeLine[] = [
    {
      label: "2015-09-1",
      content: "Technical testing",
      type: "translation",
    },
    {
      label: "2015-09-1",
      content: "Technical testing",
      type: "translation",
    },
    {
      label: "2015-09-1",
      content: "Technical testing",
      type: "translation",
    },
    {
      label: "2015-09-1",
      content: "Technical testing",
      type: "translation",
    },
  ];

  return (
    <>
      <Timeline mode={"left"} style={{ width: "100%" }}>
        {data.map((item, id) => {
          return (
            <Timeline.Item key={id} label={item.label}>
              {item.content}
            </Timeline.Item>
          );
        })}
      </Timeline>
    </>
  );
};

export default TimeLineWidget;
