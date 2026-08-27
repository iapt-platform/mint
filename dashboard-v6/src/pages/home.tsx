import { useIntl } from "react-intl";

const Widget = () => {
  const intl = useIntl();
  return (
    <>
      <title>{intl.formatMessage({ id: "columns.library.home.title" })}</title>
      home
    </>
  );
};

export default Widget;
