import { Outlet } from "react-router";

const Widget = () => {
  // TODO
  return (
    <div>
      <div>
        <Outlet />
      </div>
      <div>root layout footer</div>
    </div>
  );
};

export default Widget;
