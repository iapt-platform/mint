// src/routes/channelRoutes.ts
import { lazy } from "react";
import type { RouteObject } from "react-router";
import { tagLoader } from "../api/tag";

const WorkspaceTag = lazy(() => import("../pages/workspace/tag"));
const WorkspaceTagShow = lazy(() => import("../pages/workspace/tag/show"));
const WorkspaceTagEdit = lazy(() => import("../pages/workspace/tag/edit"));

const channelRoutes: RouteObject[] = [
  {
    path: "tag",
    handle: { id: "workspace.tag", crumb: "tag" },
    children: [
      {
        index: true,
        Component: WorkspaceTag,
      },
      {
        path: ":tagId",
        loader: tagLoader,
        handle: {
          crumb: (match: { data: { name: string } }) => match.data.name,
        },
        children: [
          {
            index: true,
            Component: WorkspaceTagShow,
            handle: { id: "workspace.tag" },
          },
          {
            path: "edit",
            Component: WorkspaceTagEdit,
            handle: { id: "workspace.tag.edit", crumb: "edit" },
          },
        ],
      },
    ],
  },
];

export default channelRoutes;
