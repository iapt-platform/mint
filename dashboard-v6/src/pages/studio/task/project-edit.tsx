import { useParams } from "react-router-dom";

import ProjectEdit from "../../../components/task/ProjectEdit";

const Widget = () => {
  const { studioname } = useParams();
  const { projectId } = useParams();

  return <ProjectEdit studioName={studioname} projectId={projectId} />;
};

export default Widget;
