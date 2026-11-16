import { useIntl } from "react-intl";
import DictStatistics from "../../../features/admin/DictStatistics";

const Widget = () => {
  const intl = useIntl();
  return (
    <>
      <title>{intl.formatMessage({ id: "admin.menu.dictionary" })}</title>
      <DictStatistics />
    </>
  );
};

export default Widget;
