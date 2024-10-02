import { Outlet } from "react-router-dom";

const Widget = () => {
  return (
    <div>
      <div>anonymous header</div>
      <div>
        <Outlet />
      </div>
      <div>anonymous footer</div>
    </div>
  );
};

export default Widget;
