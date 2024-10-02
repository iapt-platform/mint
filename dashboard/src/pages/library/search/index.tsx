import { Layout } from "antd";

import HeadBar from "../../../components/library/HeadBar";
import FooterBar from "../../../components/library/FooterBar";
import { Outlet } from "react-router-dom";

const Widget = () => {
  return (
    <>
      <Layout>
        <HeadBar />
        <Outlet />
        <FooterBar />
      </Layout>
    </>
  );
};

export default Widget;
