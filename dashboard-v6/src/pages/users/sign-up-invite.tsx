import { Card, Divider } from "antd";
import { useIntl } from "react-intl";
import { useParams } from "react-router";
import SharedLinks from "../../components/users/NonSignInSharedLinks";
import InviteSignUp from "../../components/users/SignUp";

const Widget = () => {
  const intl = useIntl();
  const { token } = useParams();

  return (
    <>
      <title>{intl.formatMessage({ id: "nut.users.sign-up.title" })}</title>
      <Card
        title={intl.formatMessage({
          id: "buttons.sign-up",
        })}
        style={{ width: 400, maxWidth: "100%" }}
      >
        <InviteSignUp token={token} />
        <Divider />
        <SharedLinks />
      </Card>
    </>
  );
};

export default Widget;
