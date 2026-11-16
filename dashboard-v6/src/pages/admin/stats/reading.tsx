import { useIntl } from "react-intl";
import ReadingStats from "../../../features/admin-stats/ReadingStats";

const Widget = () => {
  const intl = useIntl();
  return (
    <>
      <title>{intl.formatMessage({ id: "admin.menu.stats.reading" })}</title>
      <ReadingStats />
    </>
  );
};

export default Widget;
