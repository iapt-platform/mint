// src/routes/nissayaRoutes.ts
import { lazy } from "react";
import type { RouteObject } from "react-router";

const WorkspaceNissayaEnding = lazy(
  () => import("../pages/workspace/nissaya/ending")
);

const nissayaRoutes: RouteObject[] = [
  {
    path: "nissaya",
    children: [
      {
        // NissayaCard 右上角「在新标签页中打开」
        path: "ending/:ending",
        Component: WorkspaceNissayaEnding,
        handle: {
          id: "workspace.nissaya.ending",
          crumb: (match: { params: { ending?: string } }) =>
            match.params.ending,
        },
      },
    ],
  },
];

export default nissayaRoutes;
