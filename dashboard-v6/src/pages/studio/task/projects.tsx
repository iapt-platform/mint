import { useParams } from "react-router-dom";

import TaskProjects from "../../../components/task/ProjectTable";

const Widget = () => {
  const { studioname } = useParams();
  return <TaskProjects studioName={studioname} />;
};

export default Widget;
