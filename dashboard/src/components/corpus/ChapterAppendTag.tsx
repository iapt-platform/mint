import { Button, Popover } from "antd";
import { PlusOutlined } from "@ant-design/icons";

import type { ChannelFilterProps } from "../channel/ChannelList";
import ChapterTagList from "./ChapterTagList";

interface IWidget {
  filter?: ChannelFilterProps;
  progress?: number;
  lang?: string;
  type?: string;
  tags?: string[];
  onTagClick?: Function;
}
const Widget = ({
  progress = 0.9,
  lang = "zh",
  type = "translation",
  tags = [],
  onTagClick,
}: IWidget) => {
  return (
    <Popover
      content={
        <div style={{ width: 600 }}>
          <ChapterTagList
            tags={tags}
            progress={progress}
            lang={lang}
            type={type}
            onTagClick={(tag: string) => {
              if (typeof onTagClick !== "undefined") {
                onTagClick(tag);
              }
            }}
          />
        </div>
      }
      placement="bottom"
      trigger="hover"
    >
      <Button type="dashed" icon={<PlusOutlined />}>
        添加标签
      </Button>
    </Popover>
  );
};

export default Widget;
