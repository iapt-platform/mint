import { useParams } from "react-router";

import GroupEdit from "../../../features/group/GroupEdit";

const Widget = () => {
  const { teamId } = useParams(); //url 参数

  return (
    <>
      <title>Team Space</title>
      <GroupEdit groupId={teamId} />
    </>
  );
};

export default Widget;
