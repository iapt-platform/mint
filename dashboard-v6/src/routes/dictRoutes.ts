// src/routes/channelRoutes.ts
import { lazy } from "react";
import type { RouteObject } from "react-router";

const WorkspaceDict = lazy(() => import("../pages/workspace/dict/list"));

const channelRoutes: RouteObject[] = [
  {
    path: "dict",
    handle: { id: "workspace.dict", crumb: "dict" },
    children: [
      {
        index: true,
        Component: WorkspaceDict,
      },
    ],
  },
];

export default channelRoutes;
