import { useParams } from "react-router-dom";

const Widget = () => {
  const { token } = useParams();
  return <div>unlock verify {token}</div>;
};

export default Widget;
