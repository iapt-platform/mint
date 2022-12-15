import { Button } from "antd";
import { Link } from "react-router-dom";

import { useAppSelector } from "../../hooks";
import { currentUser as _currentUser } from "../../reducers/current-user";

const Widget = () => {
  const user = useAppSelector(_currentUser);

  if (typeof user !== "undefined") {
    return (
      <>
        <Link to={`/studio/${user.realName}/home`}>
          <Button type="primary">藏经阁</Button>
        </Link>
      </>
    );
  } else {
    return <></>;
  }
};

export default Widget;
