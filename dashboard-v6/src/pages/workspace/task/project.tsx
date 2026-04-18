import { useNavigate, useParams } from "react-router";

import ProjectWithTasks from "../../../components/task/ProjectWithTasks";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const currUser = useAppSelector(currentUser);

  const { projectId } = useParams();
  const navigate = useNavigate();
  return (
    <>
      <title>project</title>
      <ProjectWithTasks
        studioName={currUser?.realName}
        projectId={projectId}
        onChange={(id: string) => {
          navigate(`/workspace/task/project/${id}`);
        }}
      />
    </>
  );
};

export default Widget;
