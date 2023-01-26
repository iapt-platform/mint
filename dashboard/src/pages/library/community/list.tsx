import { useState } from "react";
import { Layout, Affix, Row, Col } from "antd";
import { Typography } from "antd";

import ChannelList from "../../../components/channel/ChannelList";
import BookTree from "../../../components/corpus/BookTree";
import ChapterFileter from "../../../components/corpus/ChapterFilter";
import ChapterList from "../../../components/corpus/ChapterList";
import ChapterTagList from "../../../components/corpus/ChapterTagList";

const { Title } = Typography;
const Widget = () => {
  // TODO
  const defaultTags: string[] = [];
  const [tags, setTags] = useState(defaultTags);

  return (
    <Row>
      <Col xs={0} xl={6}>
        <Affix offsetTop={0}>
          <Layout style={{ height: "100vh", overflowY: "scroll" }}>
            <BookTree />
          </Layout>
        </Affix>
      </Col>
      <Col xs={24} xl={14}>
        <ChapterFileter />
        <Title level={1}>{tags}</Title>
        <ChapterList tags={tags} />
      </Col>
      <Col xs={0} xl={4}>
        <ChapterTagList
          onTagClick={(key: string) => {
            setTags([key]);
          }}
        />
        <ChannelList />
      </Col>
    </Row>
  );
};

export default Widget;
