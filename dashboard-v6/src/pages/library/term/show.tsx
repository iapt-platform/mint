import { useParams } from "react-router-dom";
import TermShow from "../../../components/term/TermShow";

const Widget = () => {
  const { id } = useParams(); //url 参数
  return <TermShow wordId={id} />;
};

export default Widget;
