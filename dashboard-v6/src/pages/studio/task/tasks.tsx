import { useParams } from "react-router-dom";

import MyTasks from "../../../components/task/MyTasks";

const Widget = () => {
  const { studioname } = useParams();

  return <MyTasks studioName={studioname} />;
};

export default Widget;
