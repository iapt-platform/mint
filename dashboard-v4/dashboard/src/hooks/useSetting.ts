import { useEffect, useState } from "react";
import { GetUserSetting } from "../components/auth/setting/default";
import { useAppSelector } from "../hooks";
import { settingInfo } from "../reducers/setting";

export function useSetting(key: string) {
  const [commentaryLayout, setCommentaryLayout] = useState("column");
  const settings = useAppSelector(settingInfo);

  useEffect(() => {
    const layoutCommentary = GetUserSetting(key, settings);

    layoutCommentary &&
      typeof layoutCommentary === "string" &&
      setCommentaryLayout(layoutCommentary);
  }, [key, settings]);

  return commentaryLayout;
}
