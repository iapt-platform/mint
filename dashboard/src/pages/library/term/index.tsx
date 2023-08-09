import { Outlet } from "react-router-dom";
import { Layout } from "antd";

import HeadBar from "../../../components/library/HeadBar";
import FooterBar from "../../../components/library/FooterBar";

const Widget = () => {
  return (
    <Layout>
      <HeadBar selectedKeys="term" />
      <Outlet />
      <FooterBar />
    </Layout>
  );
};

export default Widget;
