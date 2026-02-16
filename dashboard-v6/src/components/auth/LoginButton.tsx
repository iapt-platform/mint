import { useIntl } from "react-intl";
import { Link } from "react-router-dom";

interface IWidget {
  target?: React.HTMLAttributeAnchorTarget;
}
const LoginButton = ({ target }: IWidget) => {
  const intl = useIntl();
  const url = btoa(window.location.href);

  return (
    <Link to={`/anonymous/users/sign-in?url=${url}`} target={target}>
      {intl.formatMessage({
        id: "nut.users.sign-in-up.title",
      })}
    </Link>
  );
};

export default LoginButton;
