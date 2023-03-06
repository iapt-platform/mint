import { useParams } from "react-router-dom";
import Heatmap from "../../../components/exp/Heatmap";
import StatisticCard from "../../../components/exp/StatisticCard";

import StudyTimeDualAxes from "../../../components/exp/StudyTimeDualAxes";

const Widget = () => {
  const { studioname } = useParams(); //url 参数

  return (
    <div style={{ padding: "1em" }}>
      <StatisticCard studioName={studioname} />
      <StudyTimeDualAxes studioName={studioname} />
      <Heatmap studioName={studioname} />
    </div>
  );
};

export default Widget;
