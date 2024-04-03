import { useParams } from "react-router-dom";

import InviteList from "../../../components/invite/InviteList";

const Widget = () => {
  const { studioname } = useParams(); //url 参数

  return <InviteList studioName={studioname} />;
};

export default Widget;
