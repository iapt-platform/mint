import { Outlet } from "react-router";

import Footer from "../Footer";

const Widget = () => {
  // TODO
  return (
    <div>
      <div>anonymous header</div>
      <div>
        <Outlet />
      </div>
      <div>
        anonymous layout footer
        <Footer />
      </div>
    </div>
  );
};

export default Widget;
