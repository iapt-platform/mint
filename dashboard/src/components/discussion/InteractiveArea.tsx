import { Tabs } from "antd";
import { TResType } from "./DiscussionListCard";
import Discussion from "./Discussion";

interface IWidget {
  resId?: string;
  resType?: TResType;
}
const InteractiveAreaWidget = ({ resId, resType }: IWidget) => {
  return (
    <Tabs
      size="small"
      defaultActiveKey="1"
      items={[
        {
          label: `问答`,
          key: "qa",
          children: <Discussion resId={resId} resType={resType} type="qa" />,
        },
        {
          label: `求助`,
          key: "help",
          children: <Discussion resId={resId} resType={resType} type="help" />,
        },
        {
          label: `讨论`,
          key: "discussion",
          children: (
            <Discussion resId={resId} resType={resType} type="discussion" />
          ),
        },
      ]}
    />
  );
};

export default InteractiveAreaWidget;
