// src/routes/channelRoutes.ts
import { createElement, lazy } from "react";
import type { RouteObject } from "react-router";
import { groupLoader } from "../api/group";
import RouteError from "../components/general/RouteError";

const WorkspaceTeam = lazy(() => import("../pages/workspace/team"));
const WorkspaceTeamShow = lazy(() => import("../pages/workspace/team/show"));
const WorkspaceTeamSetting = lazy(() => import("../pages/workspace/team/edit"));
const WorkspaceMembers = lazy(() => import("../pages/workspace/members"));

const channelRoutes: RouteObject[] = [
  // 当前团队空间的成员管理：只在团队空间里可用（见 features/teamspace/scope.ts）
  {
    path: "members",
    Component: WorkspaceMembers,
    handle: { id: "workspace.members", crumb: "members" },
  },
  {
    path: "team",
    handle: { id: "workspace.team", crumb: "team" },
    children: [
      {
        index: true,
        Component: WorkspaceTeam,
      },
      {
        path: ":teamId",
        loader: groupLoader,
        errorElement: createElement(RouteError, { backTo: "/workspace/team" }),
        handle: {
          crumb: (match: { data?: { name?: string } }) =>
            match.data?.name ?? "",
        },
        children: [
          {
            index: true,
            Component: WorkspaceTeamShow,
            handle: { id: "workspace.team" },
          },
          {
            path: "setting",
            Component: WorkspaceTeamSetting,
            handle: { id: "workspace.team.setting", crumb: "setting" },
          },
        ],
      },
    ],
  },
];

export default channelRoutes;
