import { Card } from "antd";

import SignUp from "../../components/users/SignUp";

const Widget = () => {
  return (
    <div
      style={{
        width: 1000,
        maxWidth: "100%",
        marginLeft: "auto",
        marginRight: "auto",
      }}
    >
      <Card title="注册">
        <SignUp />
      </Card>
    </div>
  );
};

export default Widget;
