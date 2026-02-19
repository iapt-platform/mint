import { useEffect, useState } from "react";
import { Divider, Space, Typography } from "antd";
import { TagOutlined } from "@ant-design/icons";

import ChapterFilter from "./ChapterFilter";
import ChapterList from "./ChapterList";
import ChapterTag from "./ChapterTag";
import ChapterAppendTag from "./ChapterAppendTag";

const { Title } = Typography;

interface IWidget {
  studioName?: string;
  channelId?: string;
  tag?: string[];
}

const CommunityChapterWidget = ({ studioName, tag = [] }: IWidget) => {
  const [tags, setTags] = useState<string[]>(tag);
  const [searchKey, setSearchKey] = useState<string>();
  const [progress, setProgress] = useState<number>(0.9);
  const [lang, setLang] = useState<string>("zh");
  const [type, setType] = useState<string>("translation");

  useEffect(() => {
    setTags(tag);
  }, [tag]);

  return (
    <>
      <ChapterFilter
        onSearch={setSearchKey}
        onProgressChange={(value: string) => setProgress(parseFloat(value))}
        onLangChange={setLang}
        onTypeChange={setType}
      />

      <Divider />

      <Title level={3}>
        <Space wrap>
          <TagOutlined />

          {tags.map((item) => (
            <ChapterTag
              key={item} // ✅ v6 推荐稳定 key
              data={{
                key: item,
                title: item,
              }}
              closable
              onTagClose={() => {
                setTags((prev) => prev.filter((x) => x !== item)); // ✅ 避免闭包旧值
              }}
            />
          ))}

          <ChapterAppendTag
            tags={tags}
            progress={progress}
            lang={lang}
            type={type}
            onTagClick={(tag: string) => {
              setTags((prev) => (prev.includes(tag) ? prev : [...prev, tag]));
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
          setTags([tag]);
        }}
      />
    </>
  );
};

export default CommunityChapterWidget;
