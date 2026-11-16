import { useIntl } from "react-intl";
import { useParams } from "react-router";
import NissayaEndingList from "../../features/admin/NissayaEndingList";

/** /admin/nissaya-ending 与 /admin/nissaya-ending/:relation（按关系预筛选）。 */
const Widget = () => {
  const intl = useIntl();
  const { relation } = useParams();
  return (
    <>
      <title>{intl.formatMessage({ id: "admin.menu.nissaya-ending" })}</title>
      <NissayaEndingList key={relation ?? ""} relation={relation} />
    </>
  );
};

export default Widget;
