import { useIntl } from "react-intl";
import UserList from "../../../features/admin/UserList";

const Widget = () => {
  const intl = useIntl();
  return (
    <>
      <title>{intl.formatMessage({ id: "admin.menu.users" })}</title>
      <UserList />
    </>
  );
};

export default Widget;
