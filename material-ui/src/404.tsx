import { useIntl } from "react-intl";

import Layout from "./layouts/application";

function Widget() {
  const intl = useIntl();
  return <Layout title={intl.formatMessage({ id: "404.title" })}>aaa</Layout>;
}

export default Widget;
