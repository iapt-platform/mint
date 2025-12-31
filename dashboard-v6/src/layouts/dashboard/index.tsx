import { Outlet } from "react-router";

import Footer from "../Footer";

const Widget = () => {
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
