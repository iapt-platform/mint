import SignInForm from "../../../components/nut/users/SignIn";
import SharedLinks from "../../../components/nut/users/NonSignInSharedLinks";
import { Card, Space } from "antd";

const Widget = () => {
  return (
    <div>
      <Card title="登录">
        <Space direction="vertical">
          <SignInForm />
          <SharedLinks />
        </Space>
      </Card>
    </div>
  );
};

export default Widget;
