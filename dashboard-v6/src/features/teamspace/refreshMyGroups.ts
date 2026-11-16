import type { IStudio } from "../../api/Auth";
import { fetchMyGroups } from "../../api/group-member";
import type { AppDispatch } from "../../store";
import { recallCurrStudio, studioSignIn } from "../../reducers/current-user";

/**
 * 重新取「我加入的团队」，更新头像弹窗里的账户列表，并校验当前团队空间。
 *
 * 团队要套进 IStudio 的形状：`realName` 填团队 uid——v2 接口的 `studio` / `name`
 * 参数靠 `StudioApi::getIdByName()` 解析，它对团队认的是 uid。
 */
export async function refreshMyGroups(dispatch: AppDispatch): Promise<void> {
  const groups = await fetchMyGroups();
  const studios: IStudio[] = groups.map((group) => ({
    id: group.id ?? "",
    nickName: group.name,
    studioName: group.name,
    realName: group.id,
    role: group.role,
    memberId: group.member_id,
  }));
  dispatch(studioSignIn(studios));
  dispatch(recallCurrStudio());
}
