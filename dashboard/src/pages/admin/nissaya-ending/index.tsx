import { Outlet } from "react-router-dom";
import { Layout } from "antd";
import { styleStudioContent } from "../../studio/style";
import LeftSider from "../../../components/admin/LeftSider";

const { Content } = Layout;

const Widget = () => {
  return (
    <Content style={styleStudioContent}>
      <Outlet />
    </Content>
  );
};

export default Widget;
