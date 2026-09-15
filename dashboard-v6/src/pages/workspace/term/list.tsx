import { useIntl } from "react-intl";
import TermList from "../../../components/term/TermList";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const user = useAppSelector(currentUser);
  const studioName = user?.realName;
  const intl = useIntl();

  return (
    <>
      <title>{intl.formatMessage({ id: "columns.studio.term.title" })}</title>
      <TermList studioName={studioName} />
    </>
  );
};

export default Widget;
