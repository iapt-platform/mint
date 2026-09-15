import { Card, Divider } from "antd";
import ForgotPassword from "../../components/users/ForgotPassword";
import NonSignInSharedLinks from "../../components/users/NonSignInSharedLinks";
import { useIntl } from "react-intl";

const Widget = () => {
  const intl = useIntl();
  return (
    <>
      <title>
        {intl.formatMessage({ id: "nut.users.forgot-password.title" })}
      </title>
      <Card
        title={intl.formatMessage({
          id: "buttons.forgot.password",
        })}
      >
        <ForgotPassword />
        <Divider />
        <NonSignInSharedLinks />
      </Card>
    </>
  );
};

export default Widget;
