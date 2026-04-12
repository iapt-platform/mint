import { Navigate, Outlet } from "react-router";

import Footer from "../Footer";
import { useAuth } from "../../hooks/useAuth";
import { TO_SIGN_IN } from "../../reducers/current-user";

const Widget = () => {
  const { isAuthenticated } = useAuth();

  if (!isAuthenticated) {
    return <Navigate to={TO_SIGN_IN} replace />;
  }
  // TODO
  return (
    <div>
      <div>dashboard header</div>
      <div>
        <Outlet />
      </div>
      <div>
        dashboard layout footer
        <Footer />
      </div>
    </div>
  );
};

export default Widget;
