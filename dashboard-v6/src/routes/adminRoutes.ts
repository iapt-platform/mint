// src/routes/adminRoutes.ts
//
// 管理后台（/admin）。v4 的 pages/admin 一次迁来；两个统计页是新增的。
// 外壳 layouts/admin 负责登录与 root / administrator 权限判断。
import { lazy } from "react";
import { redirect, type RouteObject } from "react-router";

const StatsReading = lazy(() => import("../pages/admin/stats/reading"));
const StatsAi = lazy(() => import("../pages/admin/stats/ai"));
const ApiMonitor = lazy(() => import("../pages/admin/api"));
const Relation = lazy(() => import("../pages/admin/relation"));
const NissayaEnding = lazy(() => import("../pages/admin/nissaya-ending"));
const Dictionary = lazy(() => import("../pages/admin/dictionary/index"));
const DictionaryPreference = lazy(() => import("../pages/admin/dictionary/preference"));
const Users = lazy(() => import("../pages/admin/users/index"));
const UsersShow = lazy(() => import("../pages/admin/users/show"));
const Invite = lazy(() => import("../pages/admin/invite"));
const AiModels = lazy(() => import("../pages/admin/ai-models"));

const adminRoutes: RouteObject[] = [
  { index: true, loader: () => redirect("/admin/stats/reading") },
  {
    path: "stats",
    handle: { id: "admin.stats", crumb: "stats" },
    children: [
      { index: true, loader: () => redirect("/admin/stats/reading") },
      { path: "reading", Component: StatsReading, handle: { id: "admin.stats.reading", crumb: "reading" } },
      { path: "ai", Component: StatsAi, handle: { id: "admin.stats.ai", crumb: "ai" } },
    ],
  },
  { path: "api", Component: ApiMonitor, handle: { id: "admin.api", crumb: "api" } },
  { path: "relation", Component: Relation, handle: { id: "admin.relation", crumb: "relation" } },
  {
    path: "nissaya-ending",
    handle: { id: "admin.nissaya-ending", crumb: "nissaya-ending" },
    children: [
      { index: true, Component: NissayaEnding },
      { path: ":relation", Component: NissayaEnding },
    ],
  },
  {
    path: "dictionary",
    handle: { id: "admin.dictionary", crumb: "dictionary" },
    children: [
      { index: true, Component: Dictionary },
      { path: "preference", Component: DictionaryPreference, handle: { id: "admin.dictionary.preference", crumb: "preference" } },
    ],
  },
  {
    path: "users",
    handle: { id: "admin.users", crumb: "users" },
    children: [
      { index: true, Component: Users },
      { path: ":id", Component: UsersShow },
    ],
  },
  { path: "invite", Component: Invite, handle: { id: "admin.invite", crumb: "invite" } },
  { path: "ai-models", Component: AiModels, handle: { id: "admin.ai-models", crumb: "ai-models" } },
];

export default adminRoutes;
