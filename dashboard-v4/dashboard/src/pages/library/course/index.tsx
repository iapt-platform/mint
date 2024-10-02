import { Outlet } from "react-router-dom";

import HeadBar from "../../../components/library/HeadBar";
import FooterBar from "../../../components/library/FooterBar";
import { Layout } from "antd";

const Widget = () => {
  return (
    <Layout>
      <HeadBar selectedKeys="course" />
      <Outlet />
      <FooterBar />
    </Layout>
  );
};

export default Widget;
