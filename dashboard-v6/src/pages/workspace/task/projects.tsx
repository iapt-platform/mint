import { useIntl } from "react-intl";
import TaskProjects from "../../../components/task/ProjectTable";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const currUser = useAppSelector(currentUser);
  const intl = useIntl();

  return (
    <>
      <title>{intl.formatMessage({ id: "labels.task.my.project" })}</title>
      <TaskProjects studioName={currUser?.realName} />
    </>
  );
};

export default Widget;
