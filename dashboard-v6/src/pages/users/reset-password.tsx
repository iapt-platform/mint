import { Card, Divider } from "antd";
import NonSignInSharedLinks from "../../components/users/NonSignInSharedLinks";
import { useParams } from "react-router";
import ResetPassword from "../../components/users/ResetPassword";
import { useIntl } from "react-intl";

const Widget = () => {
  const { token } = useParams();
  const intl = useIntl();

  return (
    <>
      <title>{intl.formatMessage({ id: "buttons.reset.password" })}</title>
      <Card
        title={intl.formatMessage({
          id: "buttons.reset.password",
        })}
        style={{ width: 400, maxWidth: "100%" }}
      >
        <ResetPassword token={token} />
        <Divider />
        <NonSignInSharedLinks />
      </Card>
    </>
  );
};

export default Widget;
