// src/routes/settingsRoutes.ts
import { lazy } from "react";
import type { RouteObject } from "react-router";

const DashboardSettingsAIModelIndex = lazy(
  () => import("../pages/dashboard/settings/ai-model/index")
);
const DashboardSettingsAIModelEdit = lazy(
  () => import("../pages/dashboard/settings/ai-model/edit")
);
const DashboardSettingsAIModelLog = lazy(
  () => import("../pages/dashboard/settings/ai-model/log")
);

const settingsRoutes: RouteObject[] = [
  {
    path: "settings",
    handle: { id: "workspace.settings", crumb: "settings" },
    children: [
      {
        path: "ai-model",
        children: [
          {
            index: true,
            handle: { id: "workspace.setting.model", crumb: "model" },
            Component: DashboardSettingsAIModelIndex,
          },
          {
            path: ":id",
            children: [
              {
                path: "edit",
                Component: DashboardSettingsAIModelEdit,
                handle: { id: "workspace.setting.model.edit", crumb: "edit" },
              },
              {
                path: "log",
                Component: DashboardSettingsAIModelLog,
                handle: { id: "workspace.setting.model.log", crumb: "log" },
              },
            ],
          },
        ],
      },
    ],
  },
];

export default settingsRoutes;
