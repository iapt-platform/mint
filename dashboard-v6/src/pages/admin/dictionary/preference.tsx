import { useIntl } from "react-intl";
import DictPreference from "../../../components/dict/DictPreference";

const Widget = () => {
  const intl = useIntl();
  return (
    <>
      <title>{intl.formatMessage({ id: "admin.menu.dictionary.preference" })}</title>
      <DictPreference />
    </>
  );
};

export default Widget;
