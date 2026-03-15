import { useParams } from "react-router";

import ProjectEdit from "../../../components/task/ProjectEdit";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const currUser = useAppSelector(currentUser);

  const { projectId } = useParams();

  return <ProjectEdit studioName={currUser?.realName} projectId={projectId} />;
};

export default Widget;
