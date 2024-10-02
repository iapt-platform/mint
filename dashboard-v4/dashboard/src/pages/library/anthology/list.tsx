import { useState } from "react";
import { Input, Tabs } from "antd";
import { Layout, Affix, Col, Row } from "antd";

import AnthologyList from "../../../components/article/AnthologyList";
import AnthologyStudioList from "../../../components/article/AnthologyStudioList";
import ArticleListPublic from "../../../components/article/ArticleListPublic";

const { Content, Header } = Layout;
const { Search } = Input;

const Widget = () => {
  const [searchKey, setSearchKey] = useState<string>();

  return (
    <Layout>
      <Header style={{ height: 200, display: "none" }}>
        <h2>composition</h2>
        <p>
          Make the Pāḷi easy to read <br />
          solution of Pāḷi glossary For translating <br />
          Pāḷi in Group Show the source reference in Pāḷi
        </p>
      </Header>
      <Affix offsetTop={0}>
        <div
          style={{
            backgroundColor: "rgba(100,100,100,0.3)",
            backdropFilter: "blur(5px)",
          }}
        >
          <Row style={{ paddingTop: "0.5em", paddingBottom: "0.5em" }}>
            <Col span="8" offset={8}>
              <Search
                placeholder="标题搜索"
                onSearch={(value: string) => {
                  setSearchKey(value);
                }}
                style={{ width: "100%" }}
              />
            </Col>
          </Row>
        </div>
      </Affix>

      <Content>
        <Row>
          <Col flex="auto"></Col>
          <Col flex="1260px">
            <Row>
              <Col span="16">
                <Tabs
                  size="small"
                  items={[
                    {
                      label: `Anthology`,
                      key: "anthology",
                      children: <AnthologyList searchKey={searchKey} />,
                    },
                    {
                      label: `Article`,
                      key: "article",
                      children: <ArticleListPublic search={searchKey} />,
                    },
                  ]}
                />
              </Col>
              <Col span="8" style={{ padding: 8 }}>
                <AnthologyStudioList />
              </Col>
            </Row>
          </Col>
          <Col flex="auto"></Col>
        </Row>
      </Content>
    </Layout>
  );
};

export default Widget;
