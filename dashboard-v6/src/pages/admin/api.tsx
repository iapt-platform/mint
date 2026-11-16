import { useIntl } from "react-intl";
import ApiDashboard from "../../features/admin/ApiDashboard";

const Widget = () => {
  const intl = useIntl();
  return (
    <>
      <title>{intl.formatMessage({ id: "admin.menu.api" })}</title>
      <ApiDashboard />
    </>
  );
};

export default Widget;
