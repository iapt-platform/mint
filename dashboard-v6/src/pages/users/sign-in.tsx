import SignInForm from "../../components/users/SignIn";
import SharedLinks from "../../components/users/NonSignInSharedLinks";
import { Card, Divider } from "antd";
import { useIntl } from "react-intl";

const Widget = () => {
  const intl = useIntl();
  return (
    <>
      <title>{intl.formatMessage({ id: "nut.users.sign-in.title" })}</title>
      <Card
        title={intl.formatMessage({
          id: "nut.users.sign-in.title",
        })}
        style={{ width: 400, maxWidth: "100%" }}
      >
        <SignInForm />
        <Divider />
        <SharedLinks />
      </Card>
    </>
  );
};

export default Widget;
