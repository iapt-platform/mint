import { useNavigate, useParams } from "react-router-dom";

import Dictionary from "../../../components/dict/Dictionary";

const Widget = () => {
  const { word } = useParams(); //url 参数
  const navigate = useNavigate();
  return (
    <Dictionary
      word={word}
      onSearch={(value: string) => {
        navigate("/dict/" + value);
      }}
    />
  );
};

export default Widget;
