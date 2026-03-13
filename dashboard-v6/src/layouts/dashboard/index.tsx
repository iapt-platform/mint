import { Navigate, Outlet } from "react-router";

import Footer from "../Footer";
import { useAuth } from "../../hooks/useAuth";

const Widget = () => {
  const { isAuthenticated } = useAuth();

  if (!isAuthenticated) {
    return <Navigate to="/anonymous/sign-in" replace />;
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
