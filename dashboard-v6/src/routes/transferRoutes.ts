// src/routes/channelRoutes.ts
import { lazy } from "react";
import type { RouteObject } from "react-router";

const WorkspaceTransfer = lazy(() => import("../pages/workspace/transfer"));

const channelRoutes: RouteObject[] = [
  {
    path: "transfer",
    handle: { id: "workspace.transfer", crumb: "transfer" },
    children: [
      {
        index: true,
        Component: WorkspaceTransfer,
      },
    ],
  },
];

export default channelRoutes;
