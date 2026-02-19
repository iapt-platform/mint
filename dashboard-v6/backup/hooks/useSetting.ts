import { useEffect, useState } from "react";
import { GetUserSetting } from "../components/auth/setting/default";
import { useAppSelector } from "../hooks";
import { settingInfo } from "../reducers/setting";

export function useSetting(key: string) {
  const [commentaryLayout, setCommentaryLayout] = useState<
    string | number | boolean | string[] | undefined
  >();
  const settings = useAppSelector(settingInfo);

  useEffect(() => {
    const layoutCommentary = GetUserSetting(key, settings);

    setCommentaryLayout(layoutCommentary);
  }, [key, settings]);

  return commentaryLayout;
}
