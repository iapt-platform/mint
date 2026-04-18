import { useIntl } from "react-intl";
import { Modal } from "antd";
import { ExclamationCircleOutlined } from "@ant-design/icons";

import { useAppSelector } from "../../hooks";
import { isGuest, TO_SIGN_IN } from "../../reducers/current-user";
import { useEffect } from "react";
import { useNavigate } from "react-router";

interface IWidget {
  enable?: boolean;
  mode?: string;
}
const LoginAlertModalWidget = ({ enable = false, mode = "read" }: IWidget) => {
  const intl = useIntl();
  const navigate = useNavigate();

  const guest = useAppSelector(isGuest);
  console.log("mode", mode);
  useEffect(() => {
    const guestMode = localStorage.getItem("guest_mode");
    if (guestMode === "true") {
      return;
    }
    if (guest && (mode !== "read" || enable === true)) {
      Modal.confirm({
        title: intl.formatMessage({
          id: "labels.no.login",
        }),
        icon: <ExclamationCircleOutlined />,
        content: intl.formatMessage({
          id: "message.auth.guest.alert",
        }),
        okText: intl.formatMessage({
          id: "buttons.sign-in",
        }),
        cancelText: intl.formatMessage({
          id: "buttons.use.as.guest",
        }),
        onOk: () => navigate(TO_SIGN_IN),
        onCancel: () => localStorage.setItem("guest_mode", "true"),
      });
    }
  }, [guest, mode, enable]);
  return <></>;
};

export default LoginAlertModalWidget;
