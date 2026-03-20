// src/routes/tipitakaRoutes.ts
import { lazy } from "react";
import type { RouteObject } from "react-router";

const WorkspaceTipitaka = lazy(
  () => import("../pages/workspace/tipitaka/bypath")
);
const WorkspaceTipitakaChapter = lazy(
  () => import("../pages/workspace/tipitaka/chapter")
);
const WorkspaceTipitakaPara = lazy(
  () => import("../pages/workspace/tipitaka/para")
);

const tipitakaRoutes: RouteObject[] = [
  {
    path: "tipitaka",
    handle: { id: "workspace.tipitaka", crumb: "tipitaka" },
    children: [
      {
        path: "lib",
        Component: WorkspaceTipitaka,
        children: [
          {
            path: ":root",
            Component: WorkspaceTipitaka,
            children: [
              {
                path: ":path",
                Component: WorkspaceTipitaka,
                children: [
                  {
                    path: ":tag",
                    Component: WorkspaceTipitaka,
                    handle: { id: "workspace.tipitaka.tag", crumb: "tag" },
                  },
                ],
              },
            ],
          },
        ],
      },
      {
        path: "chapter",
        children: [
          {
            path: ":id",
            Component: WorkspaceTipitakaChapter,
            handle: { id: "workspace.tipitaka", crumb: "chapter" },
          },
        ],
      },
      {
        path: "para",
        children: [
          {
            path: ":id",
            Component: WorkspaceTipitakaPara,
            handle: { id: "workspace.tipitaka", crumb: "para" },
          },
        ],
      },
    ],
  },
];

export default tipitakaRoutes;
