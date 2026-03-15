import TaskProjects from "../../../components/task/ProjectTable";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const currUser = useAppSelector(currentUser);

  return (
    <>
      <title>project</title>
      <TaskProjects studioName={currUser?.realName} />
    </>
  );
};

export default Widget;
