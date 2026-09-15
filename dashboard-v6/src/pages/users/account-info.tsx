import { useIntl } from "react-intl";
import ChangePassword from "../../components/users/ChangePassword";
import Profile from "../../components/users/Profile";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";

const Widget = () => {
  const user = useAppSelector(currentUser);
  const intl = useIntl();
  console.log(user?.avatar);

  return (
    <div>
      <title>
        {intl.formatMessage({ id: "pages.users.account-info.title" })}
      </title>
      <h3> account info</h3>
      <ChangePassword />
      <Profile />
    </div>
  );
};

export default Widget;
