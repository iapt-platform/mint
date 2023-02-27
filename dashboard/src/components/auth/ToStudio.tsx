import { useIntl } from "react-intl";
import { Button } from "antd";
import { Link } from "react-router-dom";

import { useAppSelector } from "../../hooks";
import { currentUser as _currentUser } from "../../reducers/current-user";

const Widget = () => {
  const intl = useIntl();

  const user = useAppSelector(_currentUser);

  if (typeof user !== "undefined") {
    return (
      <>
        <Link to={`/studio/${user.realName}/home`}>
          <Button
            type="primary"
            style={{
              paddingLeft: 18,
              paddingRight: 18,
              backgroundColor: "#52974e",
            }}
          >
            {intl.formatMessage({
              id: "columns.studio.title",
            })}
          </Button>
        </Link>
      </>
    );
  } else {
    return <></>;
  }
};

export default Widget;
