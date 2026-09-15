import { useIntl } from "react-intl";

const Widget = () => {
  const intl = useIntl();
  return (
    <>
      <title>{intl.formatMessage({ id: "pages.users.personal.title" })}</title>
      User personal
    </>
  );
};

export default Widget;
