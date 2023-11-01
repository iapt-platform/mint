import { Outlet } from "react-router-dom";

const Widget = () => {
  return (
    <div>
      <div>dashboard header</div>
      <div>
        <Outlet />
      </div>
      <div>dashboard footer</div>
    </div>
  );
};

export default Widget;
