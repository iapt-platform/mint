import ChangePassword from "../../components/users/ChangePassword";
import Profile from "../../components/users/Profile";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";

const Widget = () => {
  const user = useAppSelector(currentUser);
  console.log(user?.avatar);

  return (
    <div>
      <h3> account info</h3>
      <ChangePassword />
      <Profile />
    </div>
  );
};

export default Widget;
