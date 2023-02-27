import { Outlet } from "react-router-dom";
import { Layout } from "antd";

import FooterBar from "../../../components/library/FooterBar";

const Widget = () => {
  return (
    <Layout>
      <Outlet />
      <FooterBar />
    </Layout>
  );
};

export default Widget;
