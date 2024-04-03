import { FormattedMessage } from "react-intl";
import { Link } from "react-router-dom";
import { Divider, Space } from "antd";

const Widget = () => {
  return (
    <Space>
      <Link to="/anonymous/users/sign-in">
        <FormattedMessage id="nut.users.sign-in.title" />
      </Link>
      <Divider type="vertical" />
      <Link to="/users/sign-up">
        <FormattedMessage id="nut.users.sign-up.title" />
      </Link>
      <Divider type="vertical" />
      <Link to="/anonymous/users/forgot-password">
        <FormattedMessage id="buttons.forgot.password" />
      </Link>
    </Space>
  );
};

export default Widget;
