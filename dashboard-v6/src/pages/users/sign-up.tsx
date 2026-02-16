import { Card } from "antd";

import SignUp from "../../components/users/SignUp";
import { useIntl } from "react-intl";

const Widget = () => {
  const intl = useIntl();

  return (
    <div
      style={{
        width: 1000,
        maxWidth: "100%",
        marginLeft: "auto",
        marginRight: "auto",
      }}
    >
      <Card title={intl.formatMessage({ id: "labels.sign-up" })}>
        <SignUp />
      </Card>
    </div>
  );
};

export default Widget;
