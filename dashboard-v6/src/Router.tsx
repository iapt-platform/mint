import { lazy } from "react";
import { createBrowserRouter } from "react-router";
import { RouterProvider } from "react-router/dom";

const UsersSignIn = lazy(() => import("./pages/users/sign-in"));
const UsersPersonal = lazy(() => import("./pages/users/personal"));
const DashboardIndex = lazy(() => import("./pages/dashboard/index"));
const Home = lazy(() => import("./pages/home"));

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
          children: [{ path: "sign-in", Component: UsersSignIn }],
        },
        {
          path: "dashboard",
          Component: DashboardLayout,
          children: [
            { index: true, Component: DashboardIndex },
            {
              path: "users",
              children: [{ path: "personal", Component: UsersPersonal }],
            },
          ],
        },
        {
          path: "workspace",
          Component: WorkspaceLayout,
          children: [
            { index: true, Component: WorkspaceLayout },
            {
              path: "home",
              Component: UsersPersonal,
            },
            {
              path: "ai",
              Component: UsersPersonal,
            },
            {
              path: "tipitaka",
              Component: UsersPersonal,
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
