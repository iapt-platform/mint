// src/Router.tsx
import { lazy } from "react";
import { createBrowserRouter, redirect } from "react-router";
import { RouterProvider } from "react-router/dom";
import { testRoutes } from "./routes/testRoutes";
import { buildRouteConfig } from "./routes/buildRoutes";
import taskRoutes from "./routes/taskRouters";
import settingsRoutes from "./routes/settingsRoutes";
import anthologyRoutes from "./routes/anthologyRoutes";
import articleRoutes from "./routes/articleRoutes";
import tipitakaRoutes from "./routes/tipitakaRoutes";
import channelRoutes from "./routes/channelRoutes";
import termRoutes from "./routes/termRoutes";
import teamRoutes from "./routes/teamRoutes";
import inviteRoutes from "./routes/inviteRoutes";
import transferRoutes from "./routes/transferRoutes";
import tagRoutes from "./routes/tagRoutes";
import driverRoutes from "./routes/driverRoutes";
import dictRoutes from "./routes/dictRoutes";
import courseRoutes from "./routes/courseRoutes";
import nissayaRoutes from "./routes/nissayaRoutes";

const RootLayout = lazy(() => import("./layouts/Root"));
const AnonymousLayout = lazy(() => import("./layouts/anonymous"));
const DashboardLayout = lazy(() => import("./layouts/dashboard"));
const WorkspaceLayout = lazy(() => import("./layouts/workspace"));
const TestLayout = lazy(() => import("./layouts/test"));

const UsersSignIn = lazy(() => import("./pages/users/sign-in"));
const UsersSignUp = lazy(() => import("./pages/users/sign-up"));
const UsersSignUpInvite = lazy(() => import("./pages/users/sign-up-invite"));
const UsersForgotPassword = lazy(() => import("./pages/users/forgot-password"));
const UsersResetPassword = lazy(() => import("./pages/users/reset-password"));
const DashboardIndex = lazy(() => import("./pages/dashboard/index"));

const Home = lazy(() => import("./pages/home"));
const WorkspaceHome = lazy(() => import("./pages/workspace/home"));
const WorkspaceChat = lazy(() => import("./pages/workspace/chat"));

const router = createBrowserRouter(
  [
    {
      path: "/",
      Component: RootLayout,
      children: [
        { index: true, Component: Home },
        {
          path: "anonymous",
          Component: AnonymousLayout,
          handle: { crumb: "anonymous" },
          children: [
            {
              path: "sign-in",
              Component: UsersSignIn,
              handle: { crumb: "sign-in" },
            },
            {
              path: "sign-up",
              Component: UsersSignUp,
              handle: { crumb: "sign-up" },
            },
            {
              path: "sign-up/:token",
              Component: UsersSignUpInvite,
              handle: { crumb: "sign-up" },
            },
            // studio 邀请邮件仍由 v2（POST /v2/invite → InviteMail）发出，链接是 v4 的
            // 路径形状 /anonymous/users/sign-up/{uuid}。邀请创建迁 v3 之后删掉这条。
            {
              path: "users/sign-up/:token",
              loader: ({ params }) =>
                redirect(`/anonymous/sign-up/${params.token ?? ""}`),
            },
            { path: "forgot-password", Component: UsersForgotPassword },
            // 找回密码邮件里的链接，由后端按 mint.server.dashboard_v6_base_path 拼出
            { path: "reset-password/:token", Component: UsersResetPassword },
          ],
        },
        {
          path: "dashboard",
          Component: DashboardLayout,
          children: [{ index: true, Component: DashboardIndex }],
        },
        {
          path: "workspace",
          Component: WorkspaceLayout,
          handle: { id: "workspace.home", crumb: "workspace" },
          children: [
            { index: true, Component: WorkspaceHome },
            {
              path: "ai",
              Component: WorkspaceChat,
              handle: { id: "workspace.ai", crumb: "ai" },
            },
            ...taskRoutes,
            ...settingsRoutes,
            ...anthologyRoutes,
            ...articleRoutes,
            ...tipitakaRoutes,
            ...channelRoutes,
            ...termRoutes,
            ...teamRoutes,
            ...inviteRoutes,
            ...transferRoutes,
            ...tagRoutes,
            ...driverRoutes,
            ...dictRoutes,
            ...courseRoutes,
            ...nissayaRoutes,
          ],
        },

        // ─── Test 路由：使用 TestLayout + 自动注册 testRoutes ───────────────
        {
          path: "test",
          Component: TestLayout,
          children: [{ index: true }, ...buildRouteConfig(testRoutes)],
        },
      ],
    },
  ],
  { basename: import.meta.env.BASE_URL },
);

const Widget = () => {
  return <RouterProvider router={router} />;
};

export default Widget;
