import { useAppSelector } from "../hooks";
import { settingInfo } from "../reducers/setting";
import { GetUserSetting } from "../components/setting/default";

export function useSetting(key: string) {
  const settings = useAppSelector(settingInfo);
  return GetUserSetting(key, settings);
}
