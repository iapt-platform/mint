import { useState } from "react";
import { Affix, Row, Col, Divider, Space } from "antd";
import { Typography } from "antd";
import { TagOutlined } from "@ant-design/icons";

import ChannelList from "../../../components/channel/ChannelList";
import BookTree from "../../../components/corpus/BookTree";
import ChapterFilter from "../../../components/corpus/ChapterFilter";
import ChapterList from "../../../components/corpus/ChapterList";
import ChapterTag from "../../../components/corpus/ChapterTag";
import ChapterAppendTag from "../../../components/corpus/ChapterAppendTag";

const { Title } = Typography;
const Widget = () => {
  const [tags, setTags] = useState<string[]>([]);
  const [searchKey, setSearchKey] = useState<string>();
  const [progress, setProgress] = useState(0.9);
  const [lang, setLang] = useState("zh");
  const [type, setType] = useState("translation");
  return (
    <Row>
      <Col xs={0} sm={6} md={5}>
        <Affix offsetTop={0}>
          <div style={{ height: "100vh", overflowY: "auto" }}>
            <BookTree
              multiSelectable={false}
              onChange={(key: string[], path: string[]) => {
                if (key.length > 0) {
                  setTags(key[0].split(","));
                } else {
                  setTags([]);
                }
              }}
            />
          </div>
        </Affix>
      </Col>
      <Col xs={24} sm={18} md={14}>
        <ChapterFilter
          onSearch={(value: string) => {
            console.log("search change", value);
            setSearchKey(value);
          }}
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
            {tags.map((item, id) => {
              return (
                <ChapterTag
                  data={{
                    key: item,
                    title: item,
                  }}
                  key={id}
                  closable={true}
                  onTagClose={() => {
                    console.log("tag change");
                    setTags(tags.filter((x) => x !== item));
                  }}
                />
              );
            })}
            <ChapterAppendTag
              tags={tags}
              progress={progress}
              lang={lang}
              type={type}
              onTagClick={(tag: string) => {
                console.log("tag change");
                setTags([...tags, tag]);
              }}
            />
          </Space>
        </Title>

        <ChapterList
          searchKey={searchKey}
          tags={tags}
          progress={progress}
          lang={lang}
          type={type}
          onTagClick={(tag: string) => {
            console.log("tag change");
            setTags([tag]);
          }}
        />
      </Col>
      <Col xs={0} sm={0} md={5} style={{ padding: 5 }}>
        <ChannelList />
      </Col>
    </Row>
  );
};

export default Widget;
