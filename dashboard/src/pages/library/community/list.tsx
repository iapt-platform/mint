import { useNavigate } from "react-router-dom";
import { useState } from "react";
import { Layout, Affix, Row, Col, Divider, Space } from "antd";
import { Typography } from "antd";
import { TagOutlined } from "@ant-design/icons";
import ChannelList from "../../../components/channel/ChannelList";
import BookTree from "../../../components/corpus/BookTree";
import ChapterFilter from "../../../components/corpus/ChapterFilter";
import ChapterList from "../../../components/corpus/ChapterList";
import ChapterTagList from "../../../components/corpus/ChapterTagList";

const { Title } = Typography;
const Widget = () => {
  // TODO
  const navigate = useNavigate();

  const defaultTags: string[] = [];
  const [tags, setTags] = useState(defaultTags);
  const [progress, setProgress] = useState(0.9);
  const [lang, setLang] = useState("zh");
  const [type, setType] = useState("translation");

  return (
    <Row>
      <Col xs={0} sm={6} md={5}>
        <Affix offsetTop={0}>
          <Layout style={{ height: "100vh", overflowY: "scroll" }}>
            <BookTree
              onChange={(key: string, path: string[]) => {
                /*
                navigate(
                  `/community/list/${key.split(",").join("-").toLowerCase()}`
                );
                */
                setTags(key.split(","));

                console.log(key);
              }}
            />
          </Layout>
        </Affix>
      </Col>
      <Col xs={24} sm={18} md={14}>
        <ChapterFilter
          onProgressChange={(value: string) => {
            console.log("progress change", value);
            setProgress(parseFloat(value));
          }}
          onLangChange={(value: string) => {
            console.log("lang change", value);
            setLang(value);
          }}
          onTypeChange={(value: string) => {
            console.log("type change", value);
            setType(value);
          }}
        />
        <Divider />

        <Title level={3}>
          <Space>
            <TagOutlined />
            {tags}
          </Space>
        </Title>

        <ChapterList
          tags={tags}
          progress={progress}
          lang={lang}
          type={type}
          onTagClick={(tag: string) => {
            setTags([tag]);
          }}
        />
      </Col>
      <Col xs={0} sm={0} md={5}>
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
