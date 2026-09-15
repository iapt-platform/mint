import { Card, Collapse, Modal, Space } from "antd";
import { Typography } from "antd";
import { useState } from "react";
import { Link, useSearchParams } from "react-router";
import { useIntl } from "react-intl";

import type { ArticleMode, ArticleType } from "../../api/article";
import TypeArticle from "../article/TypeArticle";
import TypeAnthology from "../article/TypeAnthology";
import TypePali from "../article/TypePali";
import TypePage from "../article/TypePage";
import { articlePath, fullUrl } from "../../utils";

const { Text } = Typography;

export type TDisplayStyle =
  | "modal"
  | "card"
  | "toggle"
  | "link"
  | "window"
  | "popover";

interface IWidgetChapterCtl {
  type?: ArticleType;
  id?: string;
  mode?: ArticleMode;
  anthology?: string;
  book?: string;
  paragraphs?: string;
  channel?: string;
  parentChannels?: string[];
  title?: React.ReactNode;
  focus?: string | null;
  style?: TDisplayStyle;
  modalExtra?: React.ReactNode;
}

interface IArticleInner {
  type?: ArticleType;
  id?: string;
  mode: ArticleMode;
  anthology?: string;
  book?: string;
  paragraphs?: string;
  channelId?: string;
  parentChannels?: string[];
  focus?: string | null;
}

/**
 * v6 中已不再有统一的 article dispatcher（即 v4 的 components/article/Article.tsx），
 * 这里按 type 分发到对应的 v6 渲染组件。
 * 目前 v6 已迁移：article / anthology / chapter / para；
 * page / cs-para / series / term / task 等待后续迁移（TypePage 等尚未移植）。
 */
const ArticleInner = ({
  type,
  id,
  mode,
  anthology,
  book,
  paragraphs,
  channelId,
  parentChannels,
  focus,
}: IArticleInner) => {
  switch (type) {
    case "chapter":
    case "para":
      return (
        <TypePali
          type={type}
          id={id}
          mode={mode}
          channelId={channelId}
          book={book}
          para={paragraphs}
          focus={focus}
        />
      );
    case "anthology":
      return <TypeAnthology id={id} mode={mode} channelId={channelId} />;
    case "page":
      return (
        <TypePage
          articleId={id}
          mode={mode}
          channelId={channelId}
          focus={focus}
        />
      );
    case "article":
      return (
        <TypeArticle
          articleId={id}
          anthologyId={anthology}
          mode={mode}
          channelId={channelId}
          parentChannels={parentChannels}
          active
          hideInteractive
          hideTitle
          isSubWindow
        />
      );
    default:
      return null;
  }
};

export const ArticleCtl = ({
  type,
  id,
  mode = "auto",
  anthology,
  channel,
  parentChannels,
  title,
  focus,
  book,
  paragraphs,
  style = "modal",
  modalExtra,
}: IWidgetChapterCtl) => {
  const intl = useIntl();
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [searchParams] = useSearchParams();

  let currMode: ArticleMode;
  if (mode === "auto") {
    if (searchParams.get("mode") !== null) {
      currMode = searchParams.get("mode") as ArticleMode;
    } else {
      currMode = "read";
    }
  } else {
    currMode = mode;
  }

  const channelsToken = channel?.split(",").map((item) => item.split("@"));
  channelsToken?.forEach((value) =>
    sessionStorage.setItem(value[0], value[1] ?? "")
  );
  const orgChannels = channel
    ? channel.split(",").map((item) => item.split("@")[0])
    : [];
  const strUrlChannels = searchParams.get("channel");
  const urlChannels = strUrlChannels ? strUrlChannels.split("_") : [];
  const currChannels = [...orgChannels, ...urlChannels];

  const showModal = () => {
    setIsModalOpen(true);
  };

  const handleOk = () => {
    setIsModalOpen(false);
  };

  const handleCancel = () => {
    setIsModalOpen(false);
  };
  const aTitle = title ? title : "chapter" + id;
  const article = (
    <ArticleInner
      type={type}
      id={id}
      mode={currMode}
      anthology={anthology}
      book={book}
      paragraphs={paragraphs}
      channelId={currChannels.join("_")}
      parentChannels={parentChannels}
      focus={focus}
    />
  );
  let output = <></>;
  let articleLink = `${articlePath(type ?? "", id ?? "")}?mode=${currMode}`;
  articleLink += channel ? `&channel=${currChannels.join("_")}` : "";

  const OpenLink = (
    <Link to={articleLink} target="_blank">
      {intl.formatMessage(
        {
          id: "buttons.open.in.new.tab",
        },
        { item: "" }
      )}
    </Link>
  );
  switch (style) {
    case "modal":
      output = (
        <>
          <Typography.Link
            onClick={(event: React.MouseEvent<HTMLElement, MouseEvent>) => {
              if (event.ctrlKey || event.metaKey) {
                window.open(fullUrl(articleLink), "_blank");
              } else {
                showModal();
              }
            }}
          >
            {aTitle}
          </Typography.Link>
          <Modal
            width={"80%"}
            style={{ maxWidth: 1000, top: 20 }}
            title={
              <div
                style={{
                  display: "flex",
                  justifyContent: "space-between",
                  marginRight: 30,
                }}
              >
                <Text>{aTitle}</Text>
                <Space>
                  {OpenLink}
                  {modalExtra}
                </Space>
              </div>
            }
            open={isModalOpen}
            onOk={handleOk}
            onCancel={handleCancel}
            footer={[]}
          >
            {article}
          </Modal>
        </>
      );
      break;
    case "card":
      output = (
        <Card title={aTitle} extra={OpenLink}>
          {article}
        </Card>
      );
      break;
    case "toggle":
      output = (
        <Collapse bordered={false}>
          <Collapse.Panel header={`${aTitle}`} key="parent2">
            {article}
          </Collapse.Panel>
        </Collapse>
      );
      break;
    case "link":
      output = OpenLink;
      break;
    case "window":
      output = <div style={{ width: "100%" }}>{article}</div>;
      break;
    default:
      break;
  }
  return output;
};

interface IWidget {
  props: string;
}
const ArticleWidget = ({ props }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IWidgetChapterCtl;
  return <ArticleCtl {...prop} />;
};

export default ArticleWidget;
