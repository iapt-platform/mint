import { useIntl } from "react-intl";
import AgentStats from "../../../features/admin-stats/AgentStats";

const Widget = () => {
  const intl = useIntl();
  return (
    <>
      <title>{intl.formatMessage({ id: "admin.menu.stats.ai" })}</title>
      <AgentStats />
    </>
  );
};

export default Widget;
