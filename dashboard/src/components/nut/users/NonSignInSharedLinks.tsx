import { FormattedMessage } from "react-intl";
import { Link } from "react-router-dom";
import { Space } from "antd";

const Widget = () => {
  return (
    <Space>
      <Link to="/anonymous/users/sign-in">
        <FormattedMessage id="nut.users.sign-in.title" />
      </Link>
      <Link to="/anonymous/users/sign-up">
        <FormattedMessage id="nut.users.sign-up.title" />
      </Link>
    </Space>
  );
};

export default Widget;
