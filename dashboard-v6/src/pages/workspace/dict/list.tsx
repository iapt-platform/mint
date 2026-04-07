import UserDictList from "../../../components/dict/UserDictList";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const user = useAppSelector(currentUser);
  const studioName = user?.realName;
  return (
    <div>
      <title>dict</title>
      <UserDictList studioName={studioName} />
    </div>
  );
};

export default Widget;
