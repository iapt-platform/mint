import { useIntl } from "react-intl";
import Logs from "../../components/users/Logs";

const Widget = () => {
  const intl = useIntl();
  return (
    <div>
      <title>{intl.formatMessage({ id: "nut.users.logs.title" })}</title>
      logs
      <br />
      <Logs />
    </div>
  );
};

export default Widget;
