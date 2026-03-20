import InviteList from "../../../components/invite/InviteList";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const user = useAppSelector(currentUser);
  const studioName = user?.realName;

  return <InviteList studioName={studioName} />;
};

export default Widget;
