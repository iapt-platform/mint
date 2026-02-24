import { useMemo } from "react";
import { GetUserSetting } from "../components/auth/setting/default";
import { useAppSelector } from "../../src/hooks";
import { settingInfo } from "../../src/reducers/setting";

export function useSetting(key: string) {
  const settings = useAppSelector(settingInfo);
  return useMemo(() => GetUserSetting(key, settings), [key, settings]);
}
