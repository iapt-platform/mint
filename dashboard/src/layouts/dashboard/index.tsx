import { Outlet } from "react-router-dom";

const Widget = () => {
  return (
    <div>
      <div>dashboard layout header</div>
      <div>
        <Outlet />
      </div>
      <div>dashboard layout footer</div>
    </div>
  );
};

export default Widget;
