import { useParams } from "react-router-dom";

import TermShow from "../../../components/term/TermShow";

const Widget = () => {
  const { word } = useParams(); //url 参数
  return <TermShow word={word} />;
};

export default Widget;
