import { useParams } from "react-router-dom";

import UserDictList from "../../../components/dict/UserDictList";

const Widget = () => {
  const { studioname } = useParams();
  return <UserDictList studioName={studioname} />;
};

export default Widget;
