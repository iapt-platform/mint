import { Button, Popover } from "antd";
import { PlusOutlined } from "@ant-design/icons";

import type { ChannelFilterProps } from "../channel/ChannelList";
import ChapterTagList from "./ChapterTagList";
import { useIntl } from "react-intl";

interface IWidget {
  filter?: ChannelFilterProps;
  progress?: number;
  lang?: string;
  type?: string;
  tags?: string[];
  onTagClick?: Function;
}
const ChapterAppendTagWidget = ({
  progress = 0.9,
  lang = "zh",
  type = "translation",
  tags = [],
  onTagClick,
}: IWidget) => {
  const intl = useIntl();

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
      trigger="click"
    >
      <Button type="dashed" icon={<PlusOutlined />}>
        {intl.formatMessage({
          id: "buttons.add.tag",
        })}
      </Button>
    </Popover>
  );
};

export default ChapterAppendTagWidget;
