import TermList from "../../../components/term/TermList";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const user = useAppSelector(currentUser);
  const studioName = user?.realName;

  return <TermList studioName={studioName} />;
};

export default Widget;
