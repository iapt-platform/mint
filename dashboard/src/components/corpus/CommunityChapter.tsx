import { useEffect, useState } from "react";
import { Divider, Space } from "antd";
import { Typography } from "antd";
import { TagOutlined } from "@ant-design/icons";

import ChapterFilter from "../../components/corpus/ChapterFilter";
import ChapterList from "../../components/corpus/ChapterList";
import ChapterTag from "../../components/corpus/ChapterTag";
import ChapterAppendTag from "../../components/corpus/ChapterAppendTag";

const { Title } = Typography;

interface IWidget {
  studioName?: string;
  channelId?: string;
  tag?: string[];
}

const CommunityChapterWidget = ({
  studioName,
  channelId,
  tag = [],
}: IWidget) => {
  const [tags, setTags] = useState<string[]>(tag);
  const [searchKey, setSearchKey] = useState<string>();
  const [progress, setProgress] = useState(0.9);
  const [lang, setLang] = useState("zh");
  const [type, setType] = useState("translation");

  useEffect(() => setTags(tag), [tag]);
  return (
    <>
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
        studioName={studioName}
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
    </>
  );
};

export default CommunityChapterWidget;
