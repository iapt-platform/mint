import SignInForm from "../../components/users/SignIn";
import SharedLinks from "../../components/users/NonSignInSharedLinks";
import { Card, Space } from "antd";
import { useIntl } from "react-intl";

const Widget = () => {
  const intl = useIntl();
  return (
    <div>
      <Card
        title={intl.formatMessage({
          id: "nut.users.sign-in.title",
        })}
      >
        <Space orientation="vertical">
          <SignInForm />
          <SharedLinks />
        </Space>
      </Card>
    </div>
  );
};

export default Widget;
