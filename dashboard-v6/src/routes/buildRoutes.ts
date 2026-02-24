import type { RouteObject } from "react-router";
import type { TestRouteObject } from "./testRoutes";

/**
 * 递归地将 TestRouteObject[] 转换为 react-router RouteObject[]
 */
export function buildRouteConfig(routes: TestRouteObject[]): RouteObject[] {
  return routes.map((route) => ({
    path: route.path,
    Component: route.Component,
    children: route.children ? buildRouteConfig(route.children) : undefined,
  }));
}
