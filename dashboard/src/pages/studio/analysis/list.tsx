import { Space } from "antd";
import { useParams } from "react-router-dom";
import Heatmap from "../../../components/exp/Heatmap";
import ExpStatisticCard from "../../../components/exp/ExpStatisticCard";

import StudyTimeDualAxes from "../../../components/exp/StudyTimeDualAxes";
import { StatisticCard } from "@ant-design/pro-components";

const Widget = () => {
  const { studioname } = useParams(); //url 参数

  return (
    <div style={{ padding: "1em" }}>
      <Space direction="vertical">
        <ExpStatisticCard studioName={studioname} />
        <StatisticCard
          title="进步曲线"
          chart={<StudyTimeDualAxes studioName={studioname} />}
        />
        <StatisticCard
          title="进步日历"
          chart={<Heatmap studioName={studioname} />}
        />
      </Space>
    </div>
  );
};

export default Widget;
