import { useNavigate, useParams } from "react-router-dom";

import ProjectWithTasks from "../../../components/task/ProjectWithTasks";

const Widget = () => {
  const { studioname } = useParams();
  const { projectId } = useParams();
  const navigate = useNavigate();
  return (
    <ProjectWithTasks
      studioName={studioname}
      projectId={projectId}
      onChange={(id: string) => {
        navigate(`/studio/${studioname}/task/project/${id}`);
      }}
    />
  );
};

export default Widget;
