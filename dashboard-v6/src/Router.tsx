import { lazy } from "react";
import { createBrowserRouter } from "react-router";
import { RouterProvider } from "react-router/dom";
import { channelLoader } from "./api/channel";
import { testRoutes } from "./routes/testRoutes";
import { buildRouteConfig } from "./routes/buildRoutes";
import { anthologyLoader, articleLoader } from "./api/Article";
import { termLoader } from "./api/Term";

const RootLayout = lazy(() => import("./layouts/Root"));
const AnonymousLayout = lazy(() => import("./layouts/anonymous"));
const DashboardLayout = lazy(() => import("./layouts/dashboard"));
const WorkspaceLayout = lazy(() => import("./layouts/workspace"));

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
const WorkspaceTipitakaChapter = lazy(
  () => import("./pages/workspace/tipitaka/chapter")
);
const WorkspaceHome = lazy(() => import("./pages/workspace/home"));
const WorkspaceChat = lazy(() => import("./pages/workspace/chat"));

const WorkspaceTerm = lazy(() => import("./pages/workspace/term/list"));
const WorkspaceTermShow = lazy(() => import("./pages/workspace/term/show"));
const WorkspaceTermEdit = lazy(() => import("./pages/workspace/term/edit"));

// 文集
const WorkspaceAnthologyList = lazy(
  () => import("./pages/workspace/anthology")
);
const WorkspaceAnthologyShow = lazy(
  () => import("./pages/workspace/anthology/show")
);
const WorkspaceAnthologyEdit = lazy(
  () => import("./pages/workspace/anthology/edit")
);

// 文章
const WorkspaceArticleList = lazy(() => import("./pages/workspace/article"));
const WorkspaceArticleShow = lazy(
  () => import("./pages/workspace/article/show")
);

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
              path: "anthology",
              handle: {
                id: "workspace.anthology",
                crumb: "anthology",
              },
              children: [
                {
                  index: true,
                  Component: WorkspaceAnthologyList,
                },
                {
                  path: ":anthologyId",
                  loader: anthologyLoader,
                  handle: {
                    crumb: (match: { data: { title: string } }) =>
                      match.data.title,
                  },
                  children: [
                    { index: true, Component: WorkspaceAnthologyShow },
                    {
                      path: "edit",
                      handle: {
                        crumb: "edit",
                      },
                      Component: WorkspaceAnthologyEdit,
                    },
                    {
                      path: ":articleId",
                      Component: WorkspaceArticleShow,
                    },
                  ],
                },
              ],
            },
            {
              path: "article",
              handle: {
                id: "workspace.article",
                crumb: "article",
              },
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
                    crumb: (match: { data: { title: string } }) =>
                      match.data.title,
                  },
                },
              ],
            },
            {
              path: "tipitaka",
              handle: { id: "workspace.tipitaka", crumb: "tipitaka" },
              children: [
                {
                  path: "lib",
                  Component: WorkspaceTipitaka,
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
                  path: "chapter",
                  children: [
                    {
                      path: ":id",
                      Component: WorkspaceTipitakaChapter,
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
              children: [
                { index: true, Component: WorkspaceTerm },
                {
                  path: ":id",
                  loader: termLoader,
                  handle: {
                    crumb: (match: { data: { word: string } }) =>
                      match.data.word,
                  },
                  children: [
                    { index: true, Component: WorkspaceTermShow },
                    {
                      path: "edit",
                      handle: { crumb: "edit" },
                      Component: WorkspaceTermEdit,
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
