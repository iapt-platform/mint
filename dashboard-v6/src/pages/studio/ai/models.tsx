import { useParams } from "react-router-dom";
import AiModelList from "../../../components/ai/AiModelList";

const Widget = () => {
  const { studioname } = useParams(); //url 参数
  return <AiModelList studioName={studioname} />;
};
export default Widget;
