import { Link, useParams } from "react-router-dom";
import { Layout, Space } from "antd";

import LeftSider from "../../../components/studio/LeftSider";
import { styleStudioContent } from "../style";
import { ProCard } from "@ant-design/pro-components";
import { useState } from "react";
import DiscussionListCard from "../../../components/discussion/DiscussionListCard";
import ExpTime from "../../../components/exp/ExpTime";

const { Content } = Layout;

const Widget = () => {
  const { studioname } = useParams(); //url 参数
  const [responsive, setResponsive] = useState(false);
  return (
    <Layout>
      <LeftSider selectedKeys="invite" />
      <Content style={styleStudioContent}>
        <ProCard
          title={studioname}
          extra={
            <Space>
              {"经验"}
              <ExpTime userName={studioname} />
              <Link to={`/studio/${studioname}/exp/list`}>{"详情"}</Link>
            </Space>
          }
          split={responsive ? "horizontal" : "vertical"}
          bordered
          headerBordered
        >
          <ProCard split="horizontal" colSpan="50%">
            <ProCard title="最近编辑"></ProCard>
            <ProCard title="新手入门"></ProCard>
          </ProCard>
          <ProCard split="horizontal" colSpan="50%">
            <ProCard title="讨论">
              <div style={{ minHeight: 360 }}>
                <DiscussionListCard userId="ddd" />
              </div>
            </ProCard>
          </ProCard>
        </ProCard>
      </Content>
    </Layout>
  );
};

export default Widget;
