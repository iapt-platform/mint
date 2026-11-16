import { useAppSelector } from "../hooks";
import {
  currentStudio,
  currentUser,
} from "../reducers/current-user";

/**
 * 当前「以谁的名义」浏览资源：团队空间里是 group，否则是登录用户本人。
 *
 * `studioName` 直接传给 v2 接口的 `studio` / `name` 参数——后端
 * `StudioApi::getIdByName()` 对用户认 username、对 group 认 uid，
 * 所以 group 这里取的是 `realName`（= group uid）。
 */
export function useActiveStudio() {
  const user = useAppSelector(currentUser);
  const teamspace = useAppSelector(currentStudio);

  return {
    studioName: teamspace ? teamspace.realName : user?.realName,
    teamspace,
    isTeamspace: !!teamspace,
  };
}
