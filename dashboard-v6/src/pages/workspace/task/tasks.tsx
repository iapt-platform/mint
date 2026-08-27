import { useIntl } from "react-intl";
import MyTasks from "../../../components/task/MyTasks";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const currUser = useAppSelector(currentUser);
  const intl = useIntl();

  return (
    <>
      <title>{intl.formatMessage({ id: "labels.task.mine" })}</title>
      <MyTasks studioName={currUser?.realName} />
    </>
  );
};

export default Widget;
