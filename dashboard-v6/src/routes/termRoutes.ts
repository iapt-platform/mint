// src/routes/termRoutes.ts
import { lazy } from "react";
import type { RouteObject } from "react-router";
import { termLoader } from "../api/Term";

const WorkspaceTerm = lazy(() => import("../pages/workspace/term/list"));
const WorkspaceTermShow = lazy(() => import("../pages/workspace/term/show"));
const WorkspaceTermEdit = lazy(() => import("../pages/workspace/term/edit"));

const termRoutes: RouteObject[] = [
  {
    path: "term",
    handle: { id: "workspace.term", crumb: "term" },
    children: [
      {
        index: true,
        Component: WorkspaceTerm,
      },
      {
        path: ":id",
        loader: termLoader,
        handle: {
          crumb: (match: { data: { word: string } }) => match.data.word,
        },
        children: [
          {
            index: true,
            Component: WorkspaceTermShow,
          },
          {
            path: "edit",
            Component: WorkspaceTermEdit,
            handle: { id: "workspace.term.edit", crumb: "edit" },
          },
        ],
      },
    ],
  },
];

export default termRoutes;
