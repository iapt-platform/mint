import { useIntl } from "react-intl";
import SystemModels from "../../features/admin/SystemModels";

const Widget = () => {
  const intl = useIntl();
  return (
    <>
      <title>{intl.formatMessage({ id: "admin.menu.ai-models" })}</title>
      <SystemModels />
    </>
  );
};

export default Widget;
