// src/routes/courseRoutes.ts
import { lazy } from "react";
import type { RouteObject } from "react-router";

import { courseLoader } from "../api/course";

const WorkspaceCourseList = lazy(() => import("../pages/workspace/course"));
const WorkspaceCourseShow = lazy(
  () => import("../pages/workspace/course/show")
);
const WorkspaceCourseSetting = lazy(
  () => import("../pages/workspace/course/edit")
);

const courseRoutes: RouteObject[] = [
  {
    path: "course",
    handle: { id: "workspace.course", crumb: "course" },
    children: [
      {
        index: true,
        Component: WorkspaceCourseList,
      },
      {
        path: ":courseId",
        loader: courseLoader,
        handle: {
          crumb: (match: { data: { title: string } }) => match.data.title,
        },
        children: [
          {
            index: true,
            Component: WorkspaceCourseShow,
          },
          {
            path: "setting",
            Component: WorkspaceCourseSetting,
            handle: { id: "workspace.course.setting", crumb: "setting" },
          },
        ],
      },
    ],
  },
];

export default courseRoutes;
