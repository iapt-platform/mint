// src/routes/anthologyRoutes.ts
import { lazy } from "react";
import type { RouteObject } from "react-router";
import { anthologyLoader, articleLoader } from "../api/article";

const WorkspaceAnthologyList = lazy(
  () => import("../pages/workspace/anthology")
);
const WorkspaceAnthologyShow = lazy(
  () => import("../pages/workspace/anthology/show")
);
const WorkspaceAnthologyEdit = lazy(
  () => import("../pages/workspace/anthology/edit")
);
const WorkspaceArticleShow = lazy(
  () => import("../pages/workspace/article/show")
);

const anthologyRoutes: RouteObject[] = [
  {
    path: "anthology",
    handle: { id: "workspace.anthology", crumb: "anthology" },
    children: [
      {
        index: true,
        Component: WorkspaceAnthologyList,
      },
      {
        path: ":anthologyId",
        loader: anthologyLoader,
        handle: {
          crumb: (match: { data: { title: string } }) => match.data.title,
        },
        children: [
          { index: true, Component: WorkspaceAnthologyShow },
          {
            path: "edit",
            handle: { id: "workspace.anthology.edit", crumb: "edit" },
            Component: WorkspaceAnthologyEdit,
          },
          {
            path: ":articleId",
            loader: articleLoader,
            handle: { id: "workspace.anthology.article", crumb: "article" },
            Component: WorkspaceArticleShow,
          },
        ],
      },
    ],
  },
];

export default anthologyRoutes;
