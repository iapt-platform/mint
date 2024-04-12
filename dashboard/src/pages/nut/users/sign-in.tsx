import SignInForm from "../../../components/nut/users/SignIn";
import SharedLinks from "../../../components/nut/users/NonSignInSharedLinks";
import { Card, Space } from "antd";
import { useIntl } from "react-intl";

const Widget = () => {
  const intl = useIntl();
  return (
    <div>
      <Card
        title={intl.formatMessage({
          id: "nut.users.sign-up.title",
        })}
      >
        <Space direction="vertical">
          <SignInForm />
          <SharedLinks />
        </Space>
      </Card>
    </div>
  );
};

export default Widget;
