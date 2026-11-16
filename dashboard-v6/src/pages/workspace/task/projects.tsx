import { useIntl } from "react-intl";
import TaskProjects from "../../../components/task/ProjectTable";
import { useActiveStudio } from "../../../hooks/useActiveStudio";

const Widget = () => {
  const { studioName } = useActiveStudio();
  const intl = useIntl();

  return (
    <>
      <title>{intl.formatMessage({ id: "labels.task.my.project" })}</title>
      <TaskProjects studioName={studioName} />
    </>
  );
};

export default Widget;
