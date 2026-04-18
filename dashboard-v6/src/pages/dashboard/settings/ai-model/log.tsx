import { useParams } from "react-router";

import AiModelLogList from "../../../../components/ai-model/AiModelLogList";

const Widget = () => {
  const { id } = useParams();

  return <AiModelLogList modelId={id} />;
};

export default Widget;
