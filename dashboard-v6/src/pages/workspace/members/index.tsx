import { useIntl } from "react-intl";

import GroupMember from "../../../components/group/GroupMember";
import { useActiveStudio } from "../../../hooks/useActiveStudio";

/**
 * 当前团队空间的成员管理。个人空间下由 TeamspaceGuard 拦住，到不了这里。
 */
const Widget = () => {
  const intl = useIntl();
  const { teamspace } = useActiveStudio();

  return (
    <>
      <title>
        {`${intl.formatMessage({ id: "labels.teamspace.members" })}-${
          teamspace?.nickName ?? ""
        }`}
      </title>
      {teamspace && <GroupMember groupId={teamspace.id} />}
    </>
  );
};

export default Widget;
