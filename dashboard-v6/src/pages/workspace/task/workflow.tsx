import Workflow from "../../../components/task/Workflow";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const currUser = useAppSelector(currentUser);

  return <Workflow studioName={currUser?.realName} />;
};

export default Widget;
