import { Timeline } from "antd";
import { useEffect, useState } from "react";
import { useIntl } from "react-intl";
import { get } from "../../request";
import TimeShow from "../general/TimeShow";

interface IMilestone {
  date: string;
  event: string;
}
interface IMilestoneResponse {
  ok: boolean;
  message: string;
  data: IMilestone[];
}

interface IWidget {
  studioName?: string;
}
const TimeLineWidget = ({ studioName }: IWidget) => {
  const [milestone, setMilestone] = useState<IMilestone[]>([]);
  const intl = useIntl();

  useEffect(() => {
    if (typeof studioName === "undefined") {
      return;
    }
    get<IMilestoneResponse>(`/v2/milestone/${studioName}`).then((json) => {
      if (json.ok) {
        setMilestone(
          json.data.sort((a, b) => {
            if (a.date > b.date) {
              return -1;
            } else {
              return 1;
            }
          })
        );
      }
    });
  }, [studioName]);

  return (
    <>
      <Timeline mode="left" style={{ width: "100%" }}>
        {milestone.map((item, id) => {
          return (
            <Timeline.Item
              key={id}
              label={<TimeShow time={item.date} showIcon={false} />}
            >
              {intl.formatMessage({
                id: `labels.${item.event}`,
              })}
            </Timeline.Item>
          );
        })}
      </Timeline>
    </>
  );
};

export default TimeLineWidget;
