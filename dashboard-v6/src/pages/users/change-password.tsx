import { useIntl } from "react-intl";

const Widget = () => {
  const intl = useIntl();
  return (
    <div>
      <title>
        {intl.formatMessage({ id: "pages.users.change-password.title" })}
      </title>
      change password
    </div>
  );
};

export default Widget;
