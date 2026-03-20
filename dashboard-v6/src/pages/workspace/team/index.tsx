import { useNavigate } from "react-router";

import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";
import GroupList from "../../../features/group/GroupList";

const Widget = () => {
  const user = useAppSelector(currentUser);
  const studioName = user?.realName;
  const navigate = useNavigate();
  console.debug("channel list", studioName);
  return (
    <>
      <title>Team Space</title>
      <GroupList
        studioName={studioName}
        onSelect={(id) => {
          const url = `/workspace/team/${id}`;
          navigate(url);
        }}
        onSetting={(id) => {
          const url = `/workspace/team/${id}/setting`;
          navigate(url);
        }}
      />
    </>
  );
};

export default Widget;
