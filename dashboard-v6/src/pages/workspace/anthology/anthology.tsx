import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";
import AnthologyList from "../../../components/anthology/AnthologyList";

const Widget = () => {
  const user = useAppSelector(currentUser);
  const studioName = user?.realName;

  console.debug("channel list", studioName);
  return <AnthologyList studioName={studioName} />;
};

export default Widget;
