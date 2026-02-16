import { useIntl } from "react-intl";
import { Link } from "react-router-dom";
import { Alert } from "antd";

import { useAppSelector } from "../../hooks";
import { isGuest } from "../../reducers/current-user";
import LoginButton from "./LoginButton";

const LoginAlertWidget = () => {
  const intl = useIntl();
  const guest = useAppSelector(isGuest);

  return guest === true ? (
    <Alert
      message={intl.formatMessage({
        id: "message.auth.guest.alert",
      })}
      type="warning"
      closable
      action={<LoginButton />}
    />
  ) : (
    <></>
  );
};

export default LoginAlertWidget;
