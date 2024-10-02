import { Button, Dropdown, Modal, Space, Typography } from "antd";
import { DoubleRightOutlined, DoubleLeftOutlined } from "@ant-design/icons";
import { FolderOutlined } from "@ant-design/icons";

import { ITocPathNode } from "../corpus/TocPath";

const { Paragraph, Text } = Typography;

const EllipsisMiddle: React.FC<{
  suffixCount: number;
  maxWidth: number;
  text?: string;
}> = ({ suffixCount, maxWidth = 500, text = "" }) => {
  const start = text.slice(0, text.length - suffixCount).trim();
  const suffix = text.slice(-suffixCount).trim();
  return (
    <Text style={{ maxWidth: maxWidth }} ellipsis={{ suffix }}>
      {start}
    </Text>
  );
};

interface IWidget {
  prevTitle?: string;
  nextTitle?: string;
  path?: ITocPathNode[];
  topOfChapter?: boolean;
  endOfChapter?: boolean;
  onPrev?: Function;
  onNext?: Function;
  onPathChange?: Function;
}
const NavigateButtonWidget = ({
  prevTitle,
  nextTitle,
  topOfChapter = false,
  endOfChapter = false,
  path,
  onPrev,
  onNext,
  onPathChange,
}: IWidget) => {
  const currTitle = path && path.length > 0 ? path[path.length - 1].title : "";

  return (
    <Paragraph
      style={{
        display: "flex",
        justifyContent: "space-between",
        backdropFilter: "blur(5px)",
        backgroundColor: "rgba(200,200,200,0.2)",
        padding: 4,
      }}
    >
      <Button
        size="middle"
        icon={topOfChapter ? <FolderOutlined /> : undefined}
        disabled={typeof prevTitle === "undefined"}
        onClick={(event: React.MouseEvent<HTMLElement, MouseEvent>) => {
          if (typeof onPrev !== "undefined") {
            if (topOfChapter) {
              Modal.confirm({
                content: "已经到达章节开头，去上一个章节吗？",
                okText: "确认",
                cancelText: "取消",
                onOk: () => {
                  onPrev(event);
                },
              });
            } else {
              onPrev(event);
            }
          }
        }}
      >
        <Space>
          <DoubleLeftOutlined key="icon" />
          <EllipsisMiddle maxWidth={250} suffixCount={7} text={prevTitle} />
        </Space>
      </Button>
      <div>
        <Dropdown
          placement="top"
          trigger={["hover"]}
          menu={{
            items: path?.map((item, id) => {
              return { label: item.title, key: item.key ?? item.title };
            }),
            onClick: (e) => {
              console.debug("onPathChange", e.key);
              if (typeof onPathChange !== "undefined") {
                onPathChange(e.key);
              }
            },
          }}
        >
          <span>{currTitle}</span>
        </Dropdown>
      </div>
      <Button
        icon={endOfChapter ? <FolderOutlined /> : undefined}
        size="middle"
        disabled={typeof nextTitle === "undefined"}
        onClick={(event: React.MouseEvent<HTMLElement, MouseEvent>) => {
          if (typeof onNext !== "undefined") {
            if (endOfChapter) {
              Modal.confirm({
                content: "已经到达章节末尾，去下一个章节吗？",
                okText: "确认",
                cancelText: "取消",
                onOk: () => {
                  onNext(event);
                },
              });
            } else {
              onNext(event);
            }
          }
        }}
      >
        <Space>
          <EllipsisMiddle
            key="title"
            maxWidth={250}
            suffixCount={7}
            text={nextTitle?.substring(0, 20)}
          />
          <DoubleRightOutlined key="icon" />
        </Space>
      </Button>
    </Paragraph>
  );
};

export default NavigateButtonWidget;
