import { lazy } from "react";
import { createBrowserRouter } from "react-router";
import { RouterProvider } from "react-router/dom";
import { channelLoader } from "./api/Channel";

const UsersSignIn = lazy(() => import("./pages/users/sign-in"));
const UsersSignUp = lazy(() => import("./pages/users/sign-up"));
const UsersForgotPassword = lazy(() => import("./pages/users/forgot-password"));
const UsersResetPassword = lazy(() => import("./pages/users/reset-password"));
const UsersPersonal = lazy(() => import("./pages/users/personal"));
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

const RootLayout = lazy(() => import("./layouts/Root"));
const AnonymousLayout = lazy(() => import("./layouts/anonymous"));
const DashboardLayout = lazy(() => import("./layouts/dashboard"));
const WorkspaceLayout = lazy(() => import("./layouts/workspace"));

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
          handle: { crumb: "workspace" },
          children: [
            { index: true, Component: WorkspaceLayout },
            {
              path: "home",
              Component: UsersPersonal,
            },
            {
              path: "ai",
              Component: UsersPersonal,
              handle: { crumb: "ai" },
            },
            {
              path: "tipitaka",
              Component: WorkspaceTipitaka,
              handle: { crumb: "tipitaka" },
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
              handle: { crumb: "channel" },
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
                      Component: WorkspaceChannelSetting, // ← 新页面组件
                      handle: { crumb: "setting" },
                    },
                  ],
                },
              ],
            },
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
