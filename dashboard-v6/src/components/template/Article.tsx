import { Card, Collapse, Modal, Space } from "antd";
import { Typography } from "antd";
import { useState } from "react";
import Article, { ArticleMode, ArticleType } from "../article/Article";
import { Link, useSearchParams } from "react-router-dom";
import { fullUrl } from "../../utils";
import { useIntl } from "react-intl";

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
  console.log("anthology", anthology, channel);
  const article = (
    <Article
      active={true}
      type={type}
      articleId={id}
      anthologyId={anthology}
      book={book}
      para={paragraphs}
      channelId={currChannels.join("_")}
      parentChannels={parentChannels}
      focus={focus}
      mode={currMode}
      hideInteractive={true}
      hideTitle={true}
      isSubWindow
    />
  );
  let output = <></>;
  let articleLink = `/article/${type}/${id}?mode=${currMode}`;
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
  children?: React.ReactNode;
}
const ArticleWidget = ({ props, children }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IWidgetChapterCtl;
  return <ArticleCtl {...prop} />;
};

export default ArticleWidget;
