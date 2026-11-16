import { useIntl } from "react-intl";
import Workflow from "../../../components/task/Workflow";
import { useActiveStudio } from "../../../hooks/useActiveStudio";

const Widget = () => {
  const { studioName } = useActiveStudio();
  const intl = useIntl();

  return (
    <>
      <title>{intl.formatMessage({ id: "labels.task.workflows" })}</title>
      <Workflow studioName={studioName} />
    </>
  );
};

export default Widget;
