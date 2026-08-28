import { useIntl } from "react-intl";

const Widget = () => {
  const intl = useIntl();
  return (
    <>
      <title>{intl.formatMessage({ id: "pages.dashboard.title" })}</title>
      dashboard index
    </>
  );
};

export default Widget;
