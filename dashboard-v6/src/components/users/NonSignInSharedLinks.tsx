import { FormattedMessage } from "react-intl";
import { Link } from "react-router";
import { Divider, Space } from "antd";

const Widget = () => {
  return (
    <Space>
      <Link to="/anonymous/sign-in">
        <FormattedMessage id="buttons.sign-in" />
      </Link>
      <Divider orientation="vertical" />
      <Link to="/anonymous/sign-up">
        <FormattedMessage id="buttons.sign-up" />
      </Link>
      <Divider orientation="vertical" />
      <Link to="/anonymous/forgot-password">
        <FormattedMessage id="buttons.forgot.password" />
      </Link>
    </Space>
  );
};

export default Widget;
