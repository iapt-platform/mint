import { useIntl } from "react-intl";
import { Button } from "antd";
import { Link } from "react-router-dom";

import { useAppSelector } from "../../hooks";
import { currentUser as _currentUser } from "../../reducers/current-user";

const ToStudioWidget = () => {
  const intl = useIntl();

  const user = useAppSelector(_currentUser);

  if (typeof user !== "undefined") {
    return (
      <Button
        type="primary"
        style={{
          paddingLeft: 18,
          paddingRight: 18,
          backgroundColor: "#52974e",
        }}
      >
        <Link to={`/studio/${user.realName}/home`} target="_blank">
          {intl.formatMessage({
            id: "columns.studio.title",
          })}
        </Link>
      </Button>
    );
  } else {
    return <></>;
  }
};

export default ToStudioWidget;
