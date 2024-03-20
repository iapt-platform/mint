import { Card } from "antd";
import ForgotPassword from "../../../components/nut/users/ForgotPassword";
import NonSignInSharedLinks from "../../../components/nut/users/NonSignInSharedLinks";
import { useIntl } from "react-intl";

const Widget = () => {
  const intl = useIntl();
  return (
    <Card
      title={intl.formatMessage({
        id: "buttons.forgot.password",
      })}
    >
      <ForgotPassword />
      <NonSignInSharedLinks />
    </Card>
  );
};

export default Widget;
