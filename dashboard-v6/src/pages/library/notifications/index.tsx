import { Outlet } from "react-router-dom";
import { Layout } from "antd";

import FooterBar from "../../../components/library/FooterBar";
import HeadBar from "../../../components/library/HeadBar";

const Widget = () => {
  return (
    <Layout>
      <HeadBar />
      <Outlet />
      <FooterBar />
    </Layout>
  );
};

export default Widget;
