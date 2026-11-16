import { Tag, Tooltip } from "antd";
import { TeamOutlined } from "@ant-design/icons";
import { useIntl } from "react-intl";

import { useAppDispatch } from "../../hooks";
import { studioSignOut } from "../../reducers/current-user";
import { useActiveStudio } from "../../hooks/useActiveStudio";

/**
 * 顶栏上的「当前团队空间」标签；关闭即切回个人空间。个人空间下不渲染。
 */
const TeamspaceBadge = () => {
  const intl = useIntl();
  const dispatch = useAppDispatch();
  const { teamspace } = useActiveStudio();

  if (!teamspace) {
    return null;
  }

  return (
    <Tooltip title={intl.formatMessage({ id: "buttons.teamspace.leave" })}>
      <Tag
        data-testid="teamspace-badge"
        color="processing"
        icon={<TeamOutlined />}
        closable
        onClose={(e) => {
          e.preventDefault();
          dispatch(studioSignOut());
        }}
        style={{ marginInlineEnd: 0 }}
      >
        {intl.formatMessage(
          { id: "labels.teamspace.current" },
          { name: teamspace.nickName }
        )}
      </Tag>
    </Tooltip>
  );
};

export default TeamspaceBadge;
