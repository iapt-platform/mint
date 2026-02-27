import { lazy } from "react";
import { createBrowserRouter } from "react-router";
import { RouterProvider } from "react-router/dom";
import { channelLoader } from "./api/Channel";
import { testRoutes } from "./routes/testRoutes";
import { buildRouteConfig } from "./routes/buildRoutes";

const RootLayout = lazy(() => import("./layouts/Root"));
const AnonymousLayout = lazy(() => import("./layouts/anonymous"));
const DashboardLayout = lazy(() => import("./layouts/dashboard"));
const WorkspaceLayout = lazy(() => import("./layouts/workspace"));
const WorkspaceEditorLayout = lazy(() => import("./layouts/workspace/editor"));

const UsersSignIn = lazy(() => import("./pages/users/sign-in"));
const UsersSignUp = lazy(() => import("./pages/users/sign-up"));
const UsersForgotPassword = lazy(() => import("./pages/users/forgot-password"));
const UsersResetPassword = lazy(() => import("./pages/users/reset-password"));
const DashboardIndex = lazy(() => import("./pages/dashboard/index"));
const Home = lazy(() => import("./pages/home"));
const WorkspaceChannel = lazy(() => import("./pages/workspace/channel/list"));
const WorkspaceChannelShow = lazy(
  () => import("./pages/workspace/channel/show")
);
const WorkspaceChannelSetting = lazy(
  () => import("./pages/workspace/channel/setting")
);
const WorkspaceTipitaka = lazy(
  () => import("./pages/workspace/tipitaka/bypath")
);
const WorkspaceHome = lazy(() => import("./pages/workspace/home"));
const WorkspaceChat = lazy(() => import("./pages/workspace/chat"));

const WorkspaceTerm = lazy(() => import("./pages/workspace/term/list"));
const WorkspaceTermEdit = lazy(() => import("./pages/workspace/term/edit"));

// ↓ 新增：TestLayout
const TestLayout = lazy(() => import("./layouts/test"));

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
            { path: "forgot-password", Component: UsersForgotPassword },
          ],
        },
        {
          path: "dashboard",
          Component: DashboardLayout,
          children: [
            { index: true, Component: DashboardIndex },
            {
              path: "users",
              children: [
                { path: "reset-password", Component: UsersResetPassword },
              ],
            },
          ],
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
            {
              path: "tipitaka",
              Component: WorkspaceTipitaka,
              handle: { id: "workspace.tipitaka", crumb: "tipitaka" },
              children: [
                {
                  path: ":root",
                  Component: WorkspaceTipitaka,
                  children: [
                    {
                      path: ":path",
                      Component: WorkspaceTipitaka,
                      children: [
                        {
                          path: ":tag",
                          Component: WorkspaceTipitaka,
                        },
                      ],
                    },
                  ],
                },
              ],
            },
            {
              path: "channel",
              handle: { id: "workspace.channel", crumb: "channel" },
              children: [
                {
                  index: true,
                  Component: WorkspaceChannel,
                },
                {
                  path: ":channelId",
                  loader: channelLoader,
                  handle: {
                    crumb: (match: { data: { name: string } }) =>
                      match.data.name,
                  },
                  children: [
                    {
                      index: true,
                      Component: WorkspaceChannelShow,
                    },
                    {
                      path: "setting",
                      Component: WorkspaceChannelSetting,
                      handle: { crumb: "setting" },
                    },
                  ],
                },
              ],
            },
            {
              path: "term",
              handle: { id: "workspace.term", crumb: "term" },
              Component: WorkspaceTerm,
            },
            {
              path: "edit",
              Component: WorkspaceEditorLayout,
              handle: { crumb: "edit" },
              children: [
                {
                  path: "article",
                  children: [{ path: ":id" }],
                },
                {
                  path: "anthology",
                  children: [{ path: ":id" }],
                },
                {
                  path: "series",
                  children: [{ path: ":id" }],
                },
                {
                  path: "chapter",
                  children: [{ path: ":id" }],
                },
                {
                  path: "para",
                  children: [{ path: ":id" }],
                },
                {
                  path: "cs-para",
                  children: [{ path: ":id" }],
                },
                {
                  path: "wiki",
                  children: [
                    {
                      path: ":id",
                      Component: WorkspaceTermEdit,
                      children: [{ index: true, Component: WorkspaceTermEdit }],
                    },
                  ],
                },
              ],
            },
          ],
        },

        // ─── Test 路由：使用 TestLayout + 自动注册 testRoutes ───────────────
        {
          path: "test",
          Component: TestLayout,
          children: [
            // index: 访问 /test 时显示欢迎页（由 TestLayout 内部处理）
            { index: true },
            // 自动将 testRoutes 转换为路由配置
            ...buildRouteConfig(testRoutes),
          ],
        },
      ],
    },
  ],
  { basename: import.meta.env.BASE_URL }
);

const Widget = () => {
  return <RouterProvider router={router} />;
};

export default Widget;
