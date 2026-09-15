import { useIntl } from "react-intl";
import Workflow from "../../../components/task/Workflow";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const currUser = useAppSelector(currentUser);
  const intl = useIntl();

  return (
    <>
      <title>{intl.formatMessage({ id: "labels.task.workflows" })}</title>
      <Workflow studioName={currUser?.realName} />
    </>
  );
};

export default Widget;
