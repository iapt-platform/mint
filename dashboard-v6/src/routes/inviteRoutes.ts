// src/routes/channelRoutes.ts
import { lazy } from "react";
import type { RouteObject } from "react-router";

const WorkspaceInvite = lazy(() => import("../pages/workspace/invite"));

const channelRoutes: RouteObject[] = [
  {
    path: "invite",
    handle: { id: "workspace.invite", crumb: "invite" },
    children: [
      {
        index: true,
        Component: WorkspaceInvite,
      },
    ],
  },
];

export default channelRoutes;
