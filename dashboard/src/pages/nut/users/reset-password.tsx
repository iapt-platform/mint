import { Card } from "antd";
import ForgotPassword from "../../../components/nut/users/ForgotPassword";
import NonSignInSharedLinks from "../../../components/nut/users/NonSignInSharedLinks";

const Widget = () => {
  return (
    <Card title="重置密码">
      <ForgotPassword />
      <NonSignInSharedLinks />
    </Card>
  );
};

export default Widget;
