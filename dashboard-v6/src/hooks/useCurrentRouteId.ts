import { useMatches, type UIMatch } from "react-router";

import type { RouteHandle } from "../components/navigation/MainMenu";

/** 当前页面最深一层带 handle.id 的路由 id，如 `workspace.channel` */
export function useCurrentRouteId(): string | undefined {
  const matches = useMatches() as UIMatch<number, RouteHandle>[];
  return [...matches].reverse().find((match) => match.handle?.id)?.handle?.id;
}
