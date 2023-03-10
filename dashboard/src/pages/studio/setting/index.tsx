import { Layout } from "antd";

import LeftSider from "../../../components/studio/LeftSider";
import { styleStudioContent } from "../style";
import SettingArticle from "../../../components/auth/setting/SettingArticle";

const { Content } = Layout;

const Widget = () => {
  return (
    <Layout>
      <Layout>
        <LeftSider selectedKeys="setting" />
        <Content style={styleStudioContent}>
          <SettingArticle />
        </Content>
      </Layout>
    </Layout>
  );
};

export default Widget;
