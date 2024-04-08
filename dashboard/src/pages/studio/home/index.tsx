import { Link, useParams } from "react-router-dom";
import { Layout, Space } from "antd";

import LeftSider from "../../../components/studio/LeftSider";
import { styleStudioContent } from "../style";
import { ProCard } from "@ant-design/pro-components";
import { useState } from "react";
import DiscussionListCard from "../../../components/discussion/DiscussionListCard";
import ExpTime from "../../../components/exp/ExpTime";
import SentMyEditList from "../../../components/corpus/SentMyEditList";

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
            <ProCard title="最近打开"></ProCard>
            <ProCard title="新手入门"></ProCard>
          </ProCard>
          <ProCard
            split="horizontal"
            colSpan="50%"
            tabs={{
              items: [
                {
                  label: `讨论`,
                  key: "discussion",
                  children: (
                    <div style={{ minHeight: 360 }}>
                      <DiscussionListCard userId="ddd" />
                    </div>
                  ),
                },
                {
                  label: `最近翻译`,
                  key: "sentence",
                  children: <SentMyEditList />,
                },
              ],
            }}
          />
        </ProCard>
      </Content>
    </Layout>
  );
};

export default Widget;
