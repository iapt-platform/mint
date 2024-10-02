import { Card, Divider } from "antd";
import NonSignInSharedLinks from "../../../components/nut/users/NonSignInSharedLinks";
import { useParams } from "react-router-dom";
import ResetPassword from "../../../components/nut/users/ResetPassword";
import { useIntl } from "react-intl";

const Widget = () => {
  const { token } = useParams();
  const intl = useIntl();

  return (
    <Card
      title={intl.formatMessage({
        id: "buttons.reset.password",
      })}
    >
      <ResetPassword token={token} />
      <Divider />
      <NonSignInSharedLinks />
    </Card>
  );
};

export default Widget;
