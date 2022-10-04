import { Outlet, Link } from "react-router-dom";
import { Space } from "antd";

const Widget = () => {
  return (
    <div>
      <div>anonymous layout header</div>
      <div>
        <Outlet />

        <div>
          <Space>
            <Link to="/anonymous/users/sign-in">Sign in</Link>
            <Link to="/anonymous/users/sign-up">Sign up</Link>
            <Link to="/anonymous/users/forgot-password">Forgot password</Link>
          </Space>
        </div>
      </div>
      <div>anonymous layout footer</div>
    </div>
  );
};

export default Widget;
