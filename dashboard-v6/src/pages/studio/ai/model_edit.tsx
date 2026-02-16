import { useParams } from "react-router-dom";
import { Card } from "antd";

import GoBack from "../../../components/studio/GoBack";
import AiModelEdit from "../../../components/ai/AiModelEdit";

const Widget = () => {
  const { studioname } = useParams();
  const { modelId } = useParams(); //url 参数

  return (
    <Card
      title={
        <GoBack to={`/studio/${studioname}/ai/models/list`} title={"返回"} />
      }
    >
      <AiModelEdit studioName={studioname} modelId={modelId} />
    </Card>
  );
};

export default Widget;
