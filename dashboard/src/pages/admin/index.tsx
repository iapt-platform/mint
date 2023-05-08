import { Layout } from "antd";
import { Outlet } from "react-router-dom";
import LeftSider from "../../components/admin/LeftSider";
import HeadBar from "../../components/studio/HeadBar";

const Widget = () => {
  return (
    <div>
      <HeadBar />
      <Layout>
        <LeftSider />
        <Outlet />
      </Layout>
    </div>
  );
};

export default Widget;
