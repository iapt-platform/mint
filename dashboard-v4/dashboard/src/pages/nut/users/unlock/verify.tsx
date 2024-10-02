import { useParams } from "react-router-dom";

const Widget = () => {
  const { token } = useParams();

  return <div>verify unlock token by email({token})</div>;
};

export default Widget;
