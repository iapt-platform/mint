// src/routes/articleRoutes.ts
import { lazy } from "react";
import type { RouteObject } from "react-router";
import { articleLoader } from "../api/article";

const WorkspaceArticleList = lazy(() => import("../pages/workspace/article"));
const WorkspaceArticleShow = lazy(
  () => import("../pages/workspace/article/show")
);

const articleRoutes: RouteObject[] = [
  {
    path: "article",
    handle: { id: "workspace.article", crumb: "article" },
    children: [
      {
        index: true,
        Component: WorkspaceArticleList,
      },
      {
        path: ":articleId",
        Component: WorkspaceArticleShow,
        loader: articleLoader,
        handle: {
          crumb: (match: { data: { title: string } }) => match.data.title,
        },
      },
    ],
  },
];

export default articleRoutes;
