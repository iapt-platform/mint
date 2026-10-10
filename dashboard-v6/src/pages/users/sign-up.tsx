import { Card, Divider } from "antd";
import { useIntl } from "react-intl";
import SharedLinks from "../../components/users/NonSignInSharedLinks";
import SelfSignUp from "../../components/users/SelfSignUp";

const Widget = () => {
  const intl = useIntl();

  return (
    <>
      <title>{intl.formatMessage({ id: "nut.users.sign-up.title" })}</title>
      <Card
        title={intl.formatMessage({
          id: "buttons.sign-up",
        })}
        style={{ width: 760, maxWidth: "100%" }}
      >
        <SelfSignUp />
        <Divider />
        <SharedLinks />
      </Card>
    </>
  );
};

export default Widget;
