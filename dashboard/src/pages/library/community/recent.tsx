import { Outlet, Link } from "react-router-dom";
import { Space } from "antd";

const Widget = () => {
  return (
    <div>
      <div>我的阅读</div>
      <div>
        <Outlet />
        <div>
          <Space>
            <Link to="/">Home</Link>
            <Link to="/community">社区</Link>
          </Space>
        </div>
      </div>
      <div>底部区域</div>
    </div>
  );
};

export default Widget;
