import { useIntl } from "react-intl";
import CreditAdmin from "../../features/admin-credits/CreditAdmin";

const Widget = () => {
  const intl = useIntl();
  return (
    <>
      <title>{intl.formatMessage({ id: "admin.menu.credits" })}</title>
      <CreditAdmin />
    </>
  );
};

export default Widget;
