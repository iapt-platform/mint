import { useParams } from "react-router-dom";

const Widget = () => {
  const { token } = useParams();
  return <div>reset password {token}</div>;
};

export default Widget;
