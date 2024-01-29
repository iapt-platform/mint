import { Typography, Divider, Button, Skeleton, Space, Dropdown } from "antd";
import {
  ReloadOutlined,
  MoreOutlined,
  InboxOutlined,
  EditOutlined,
  FileOutlined,
  CopyOutlined,
} from "@ant-design/icons";

import MdView from "../template/MdView";
import TocPath, { ITocPathNode } from "../corpus/TocPath";
import PaliChapterChannelList from "../corpus/PaliChapterChannelList";
import { ArticleType } from "./Article";
import VisibleObserver from "../general/VisibleObserver";
import { IStudio } from "../auth/StudioName";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";
import AddToAnthology from "./AddToAnthology";
import { useState } from "react";
import { fullUrl } from "../../utils";
import { ArticleTplModal } from "../template/Builder/ArticleTpl";

const { Paragraph, Title, Text } = Typography;
export interface IFirstAnthology {
  id: string;
  title: string;
  count: number;
}
export interface IWidgetArticleData {
  id?: string;
  title?: string;
  subTitle?: string;
  summary?: string | null;
  content?: string;
  html?: string[];
  path?: ITocPathNode[];
  created_at?: string;
  updated_at?: string;
  owner?: IStudio;
  channels?: string[];
  type?: ArticleType;
  articleId?: string;
  remains?: boolean;
  anthology?: IFirstAnthology;
  onEnd?: Function;
  onPathChange?: Function;
  onEdit?: Function;
}

const ArticleViewWidget = ({
  id,
  title = "",
  subTitle,
  summary,
  content,
  html = [],
  path = [],
  created_at,
  updated_at,
  owner,
  channels,
  type,
  articleId,
  anthology,
  onEnd,
  remains,
  onPathChange,
  onEdit,
}: IWidgetArticleData) => {
  console.log("ArticleViewWidget render");
  const user = useAppSelector(currentUser);
  const [addToAnthologyOpen, setAddToAnthologyOpen] = useState(false);
  const [tplOpen, setTplOpen] = useState(false);

  let currChannelList = <></>;
  switch (type) {
    case "chapter":
      const chapterProps = articleId?.split("-");
      if (typeof chapterProps === "object" && chapterProps.length > 0) {
        currChannelList = (
          <PaliChapterChannelList
            para={{
              book: parseInt(chapterProps[0]),
              para: parseInt(chapterProps[1]),
            }}
            channelId={channels}
            openTarget="_self"
          />
        );
      }

      break;

    default:
      break;
  }
  return (
    <>
      <div style={{ textAlign: "right" }}>
        <Button
          type="link"
          shape="round"
          size="small"
          icon={<ReloadOutlined />}
        />
        <Dropdown
          menu={{
            items: [
              {
                label: "添加到文集",
                key: "add_to_anthology",
                icon: <InboxOutlined />,
                disabled: type === "article" ? false : true,
              },
              {
                label: "编辑",
                key: "edit",
                icon: <EditOutlined />,
                disabled: user && type === "article" ? false : true,
              },
              {
                label: "在Studio中打开",
                key: "open-studio",
                icon: <EditOutlined />,
                disabled: user && type === "article" ? false : true,
              },
              {
                label: "获取文章引用模版",
                key: "tpl",
                icon: <FileOutlined />,
                disabled: user && type === "article" ? false : true,
              },
              {
                label: "创建副本",
                key: "fork",
                icon: <CopyOutlined />,
                disabled: user && type === "article" ? false : true,
              },
            ],
            onClick: ({ key }) => {
              console.log(`Click on item ${key}`);
              switch (key) {
                case "add_to_anthology":
                  setAddToAnthologyOpen(true);
                  break;
                case "fork":
                  const url = `/studio/${user?.nickName}/article/create?parent=${articleId}`;
                  window.open(fullUrl(url), "_blank");
                  break;
                case "tpl":
                  setTplOpen(true);
                  break;
                case "edit":
                  if (typeof onEdit !== "undefined") {
                    onEdit();
                  }
                  break;
              }
            },
          }}
          placement="bottomRight"
        >
          <Button
            onClick={(e) => e.preventDefault()}
            icon={<MoreOutlined />}
            size="small"
            type="link"
          />
        </Dropdown>
      </div>

      <Space direction="vertical">
        <TocPath
          data={path}
          channels={channels}
          onChange={(
            node: ITocPathNode,
            e: React.MouseEvent<HTMLSpanElement | HTMLAnchorElement, MouseEvent>
          ) => {
            if (typeof onPathChange !== "undefined") {
              onPathChange(node, e);
            }
          }}
        />

        <Title level={4}>
          <div
            dangerouslySetInnerHTML={{
              __html: title ? title : "",
            }}
          />
        </Title>
        <Text type="secondary">{subTitle}</Text>
        {currChannelList}
        <Paragraph ellipsis={{ rows: 2, expandable: true, symbol: "more" }}>
          {summary}
        </Paragraph>
        <Divider />
      </Space>
      {html
        ? html.map((item, id) => {
            return (
              <div key={id}>
                <MdView className="pcd_article" html={item} />
              </div>
            );
          })
        : content}
      {remains ? (
        <>
          <VisibleObserver
            onVisible={(visible: boolean) => {
              console.log("visible", visible);
              if (visible && typeof onEnd !== "undefined") {
                onEnd();
              }
            }}
          />
          <Skeleton title={{ width: 200 }} paragraph={{ rows: 5 }} active />
        </>
      ) : undefined}

      {articleId ? (
        <AddToAnthology
          open={addToAnthologyOpen}
          onClose={(isOpen: boolean) => setAddToAnthologyOpen(isOpen)}
          articleIds={[articleId]}
        />
      ) : undefined}
      {type === "article" ? (
        <ArticleTplModal
          title={title}
          type="article"
          id={articleId}
          open={tplOpen}
          onOpenChange={(visible: boolean) => setTplOpen(visible)}
        />
      ) : (
        <></>
      )}
    </>
  );
};

export default ArticleViewWidget;
