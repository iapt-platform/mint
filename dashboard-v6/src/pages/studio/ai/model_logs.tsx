import { useParams } from "react-router-dom";
import AiModelLogList from "../../../components/ai/AiModelLogList";

const Widget = () => {
  const { modelId } = useParams(); //url 参数
  return <AiModelLogList modelId={modelId} />;
};
export default Widget;
