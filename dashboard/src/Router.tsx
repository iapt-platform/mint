import { Route, Routes } from "react-router-dom";

import Anonymous from "./layouts/anonymous";
import Dashboard from "./layouts/dashboard";

import NutUsersSignIn from "./pages/nut/users/sign-in";
import NutUsersSignUp from "./pages/nut/users/sign-up";
import NutUsersUnlockNew from "./pages/nut/users/unlock/new";
import NutUsersUnlockVerify from "./pages/nut/users/unlock/verify";
import NutUsersResetPassword from "./pages/nut/users/reset-password";
import NutUsersForgotPassword from "./pages/nut/users/forgot-password";
import NutUsersChangePassword from "./pages/nut/users/change-password";
import NutUsersAccountInfo from "./pages/nut/users/account-info";
import NutUsersLogs from "./pages/nut/users/logs";
import NutForbidden from "./pages/nut/forbidden";
import NutNotFound from "./pages/nut/not-found";
import NutSwitchLanguage from "./pages/nut/switch-languages";
import NutHome from "./pages/nut";

const Widget = () => {
  return (
    <Routes>
      <Route path="anonymous" element={<Anonymous />}>
        <Route path="users">
          <Route path="sign-in" element={<NutUsersSignIn />} />
          <Route path="sign-up" element={<NutUsersSignUp />} />

          <Route path="unlock">
            <Route path="new" element={<NutUsersUnlockNew />} />
            <Route path="verify/:token" element={<NutUsersUnlockVerify />} />
          </Route>
          <Route
            path="reset-password/:token"
            element={<NutUsersResetPassword />}
          />
          <Route path="forgot-password" element={<NutUsersForgotPassword />} />
        </Route>
      </Route>

      <Route path="dashboard" element={<Dashboard />}>
        <Route path="users">
          <Route path="change-password" element={<NutUsersChangePassword />} />
          <Route path="logs" element={<NutUsersLogs />} />
          <Route path="account-info" element={<NutUsersAccountInfo />} />
        </Route>
      </Route>
      <Route path="switch-language" element={<NutSwitchLanguage />} />
      <Route path="forbidden" element={<NutForbidden />} />
      <Route path="" element={<NutHome />} />
      <Route path="*" element={<NutNotFound />} />
    </Routes>
  );
};

export default Widget;
