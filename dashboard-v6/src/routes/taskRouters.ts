// src/routes/taskRouters.ts
import { lazy } from "react";
import type { RouteObject } from "react-router";

// 懒加载页面组件
const hall = lazy(() => import("../pages/workspace/task/hall"));
const pending = lazy(() => import("../pages/workspace/task/pending"));
const list = lazy(() => import("../pages/workspace/task/tasks"));
const projects = lazy(() => import("../pages/workspace/task/projects"));
const project = lazy(() => import("../pages/workspace/task/project"));
const projectEdit = lazy(() => import("../pages/workspace/task/project-edit"));
const workflows = lazy(() => import("../pages/workspace/task/workflow"));

const taskRoutes: RouteObject[] = [
  {
    path: "task",
    handle: { id: "workspace.task", crumb: "task" },
    children: [
      {
        path: "hall",
        Component: hall,
        handle: { id: "workspace.task.hall", crumb: "hall" }, // 同理也缺这个
      },
      {
        path: "pending",
        Component: pending,
        handle: { id: "workspace.task.pending", crumb: "pending" }, // 同理也缺这个
      },
      {
        path: "list",
        Component: list,
        handle: { id: "workspace.task.list", crumb: "list" },
      },
      {
        path: "project",
        handle: { id: "workspace.task.project", crumb: "project" },
        children: [
          { index: true, Component: projects },
          {
            path: ":projectId",
            Component: project,
            handle: { id: "workspace.task.project", crumb: "project" }, // ✅ 加这里
            children: [
              {
                path: "edit",
                Component: projectEdit,
                handle: { id: "workspace.task.project", crumb: "edit" }, // ✅ edit 页也需要
              },
            ],
          },
        ],
      },
      {
        path: "workflows",
        Component: workflows,
        handle: { id: "workspace.task.workflows", crumb: "workflows" }, // 同理
      },
    ],
  },
];

export default taskRoutes;
