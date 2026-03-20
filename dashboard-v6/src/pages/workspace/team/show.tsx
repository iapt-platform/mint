import { useNavigate, useParams } from "react-router";

import GroupShow from "../../../features/group/GroupShow";

const Widget = () => {
  const { teamId } = useParams(); //url 参数
  const navigate = useNavigate();

  return (
    <>
      <title>Team Space</title>
      <GroupShow
        teamId={teamId}
        onSetting={() => navigate(`/workspace/team/${teamId}/setting`)}
      />
    </>
  );
};

export default Widget;
