import { Card } from "antd";
import { useIntl } from "react-intl";
import { useParams } from "react-router-dom";
import SharedLinks from "../../../components/nut/users/NonSignInSharedLinks";
import SignUp from "../../../components/nut/users/SignUp";

const Widget = () => {
  const intl = useIntl();
  const { token } = useParams(); //url 参数

  return (
    <Card
      title={intl.formatMessage({
        id: "nut.users.sign-up.title",
      })}
    >
      <SignUp token={token} />
      <SharedLinks />
    </Card>
  );
};

export default Widget;
