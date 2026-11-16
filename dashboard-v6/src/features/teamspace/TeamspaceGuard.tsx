import { Button, Result } from "antd";
import { useIntl } from "react-intl";

import { useAppDispatch } from "../../hooks";
import { studioSignOut } from "../../reducers/current-user";
import { useActiveStudio } from "../../hooks/useActiveStudio";
import { useCurrentRouteId } from "../../hooks/useCurrentRouteId";
import { TEAMSPACE_HINT, teamspaceAvailability } from "./scope";

/**
 * 拦住当前空间下不能用的页面（菜单置灰之外，直接输入地址也进不去）：
 * 团队空间里拦个人专属与尚未支持的模块，个人空间里拦只属于团队空间的页面。
 */
const TeamspaceGuard = ({ children }: { children: React.ReactNode }) => {
  const intl = useIntl();
  const dispatch = useAppDispatch();
  const routeId = useCurrentRouteId();
  const { isTeamspace } = useActiveStudio();

  const availability = teamspaceAvailability(routeId, isTeamspace);
  if (availability === "available") {
    return <>{children}</>;
  }

  return (
    <div data-testid="teamspace-blocked">
      <Result
        status="info"
        title={intl.formatMessage({ id: TEAMSPACE_HINT[availability] })}
        extra={
          isTeamspace ? (
            <Button type="primary" onClick={() => dispatch(studioSignOut())}>
              {intl.formatMessage({ id: "buttons.teamspace.leave" })}
            </Button>
          ) : undefined
        }
      />
    </div>
  );
};

export default TeamspaceGuard;
