import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

import List from "../../../components/course/List";

const Widget = () => {
  const user = useAppSelector(currentUser);
  const studioName = user?.realName;
  return <List studioName={studioName} />;
};

export default Widget;
