// src/routes/discussionRoutes.ts
import { lazy } from "react";
import type { RouteObject } from "react-router";

const WorkspaceDiscussionTopic = lazy(
  () => import("../pages/workspace/discussion/topic"),
);
const WorkspaceDiscussionShow = lazy(
  () => import("../pages/workspace/discussion/show"),
);

/**
 * 讨论页：单个话题（「复制链接」的去处）与某个对象上的全部讨论（讨论抽屉的「新窗口打开」）。
 */
const discussionRoutes: RouteObject[] = [
  {
    path: "discussion",
    handle: { id: "workspace.discussion", crumb: "discussion" },
    children: [
      { path: "topic/:topicId", Component: WorkspaceDiscussionTopic },
      { path: "show/:type/:id", Component: WorkspaceDiscussionShow },
    ],
  },
];

export default discussionRoutes;
