import { useParams } from "react-router-dom";

const Widget = () => {
  const { token } = useParams();
  return <div>confirm verify {token}</div>;
};

export default Widget;
