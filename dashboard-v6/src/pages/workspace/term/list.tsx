import { useIntl } from "react-intl";
import TermList from "../../../components/term/TermList";
import { useActiveStudio } from "../../../hooks/useActiveStudio";

const Widget = () => {
  const { studioName } = useActiveStudio();
  const intl = useIntl();

  return (
    <>
      <title>{intl.formatMessage({ id: "columns.studio.term.title" })}</title>
      <TermList studioName={studioName} />
    </>
  );
};

export default Widget;
