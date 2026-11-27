// src/routes/termRoutes.ts
import { createElement, lazy } from "react";
import type { RouteObject } from "react-router";
import { termLoader } from "../api/Term";
import RouteError from "../components/general/RouteError";

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
        errorElement: createElement(RouteError, { backTo: "/workspace/term" }),
        handle: {
          // loader 失败时 data 为空（错误页那时也要渲染面包屑）
          crumb: (match: { data?: { word?: string } }) =>
            match.data?.word ?? "",
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
