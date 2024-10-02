import { lazy } from "react";
import { Route, Routes } from "react-router-dom";

import Anonymous from "./layouts/anonymous";
import Dashboard from "./layouts/dashboard";

const Home = lazy(() => import("./pages/home"));
const NotFound = lazy(() => import("./pages/not-found"));
const Forbidden = lazy(() => import("./pages/forbidden"));
const Loading = lazy(() => import("./pages/loading"));
const UsersSignIn = lazy(() => import("./pages/users/sign-in"));
const UsersSignUp = lazy(() => import("./pages/users/sign-up"));
const UsersLogs = lazy(() => import("./pages/users/logs"));
const UsersConfirmNew = lazy(() => import("./pages/users/confirm/new"));
const UsersConfirmVerify = lazy(() => import("./pages/users/confirm/verify"));
const UsersUnlockNew = lazy(() => import("./pages/users/unlock/new"));
const UsersUnlockVerify = lazy(() => import("./pages/users/unlock/verify"));
const UsersForgotPassword = lazy(() => import("./pages/users/forgot-password"));
const UsersResetPassword = lazy(() => import("./pages/users/reset-password"));

const Widget = () => {
  return (
    <Routes>
      <Route path="anonymous" element={<Anonymous />}>
        <Route path="users">
          <Route path="sign-in" element={<UsersSignIn />} />
          <Route path="sign-up" element={<UsersSignUp />} />
          <Route path="confirm">
            <Route path="new" element={<UsersConfirmNew />} />
            <Route path="verify/:token" element={<UsersConfirmVerify />} />
          </Route>
          <Route path="unlock">
            <Route path="new" element={<UsersUnlockNew />} />
            <Route path="verify/:token" element={<UsersUnlockVerify />} />
          </Route>
          <Route
            path="reset-password/:token"
            element={<UsersResetPassword />}
          />
          <Route path="forgot-password" element={<UsersForgotPassword />} />
        </Route>
      </Route>
      <Route path="dashboard" element={<Dashboard />}>
        <Route path="users">
          <Route path="logs" element={<UsersLogs />} />
        </Route>
      </Route>
      <Route path="loading" element={<Loading />} />
      <Route path="forbidden" element={<Forbidden />} />
      <Route path="" element={<Home />} />
      <Route path="*" element={<NotFound />} />
    </Routes>
  );
};

export default Widget;
