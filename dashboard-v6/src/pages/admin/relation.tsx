import { useIntl } from "react-intl";
import RelationList from "../../features/admin/RelationList";

const Widget = () => {
  const intl = useIntl();
  return (
    <>
      <title>{intl.formatMessage({ id: "admin.menu.relation" })}</title>
      <RelationList />
    </>
  );
};

export default Widget;
