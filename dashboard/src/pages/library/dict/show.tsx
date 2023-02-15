import { useParams } from "react-router-dom";

import Dictionary from "../../../components/dict/Dictionary";

const Widget = () => {
  const { word } = useParams(); //url 参数
  return <Dictionary word={word} />;
};

export default Widget;
