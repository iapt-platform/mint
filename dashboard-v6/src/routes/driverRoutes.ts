// src/routes/channelRoutes.ts
import { lazy } from "react";
import type { RouteObject } from "react-router";

const WorkspaceDriver = lazy(() => import("../pages/workspace/driver/list"));

const channelRoutes: RouteObject[] = [
  {
    path: "driver",
    handle: { id: "workspace.driver", crumb: "driver" },
    children: [
      {
        index: true,
        Component: WorkspaceDriver,
      },
    ],
  },
];

export default channelRoutes;
