import { Card } from "antd";
import { useIntl } from "react-intl";
import { useParams } from "react-router";
import SharedLinks from "../../components/users/NonSignInSharedLinks";
import SignUp from "../../components/users/SignUp";

const Widget = () => {
  const intl = useIntl();
  const { token } = useParams(); //url 参数

  return (
    <Card
      title={intl.formatMessage({
        id: "buttons.sign-up",
      })}
    >
      <SignUp token={token} />
      <SharedLinks />
    </Card>
  );
};

export default Widget;
