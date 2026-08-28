// src/routes/tipitakaRoutes.ts
import { lazy } from "react";
import type { RouteObject } from "react-router";
import { chapterLoader, paraLoader } from "../api/pali-text";
import { csParaLoader } from "../api/article";

const WorkspaceTipitaka = lazy(
  () => import("../pages/workspace/tipitaka/bypath")
);
const WorkspaceTipitakaChapter = lazy(
  () => import("../pages/workspace/tipitaka/chapter")
);
const WorkspaceTipitakaPara = lazy(
  () => import("../pages/workspace/tipitaka/para")
);
const WorkspaceTipitakaCsPara = lazy(
  () => import("../pages/workspace/tipitaka/cs-para")
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
            loader: chapterLoader,
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
            loader: paraLoader,
            handle: { id: "workspace.tipitaka", crumb: "para" },
          },
        ],
      },
      {
        path: "cs-para",
        children: [
          {
            path: ":id",
            Component: WorkspaceTipitakaCsPara,
            loader: csParaLoader,
            handle: { id: "workspace.tipitaka.cs-para", crumb: "cs-para" },
          },
        ],
      },
    ],
  },
];

export default tipitakaRoutes;
