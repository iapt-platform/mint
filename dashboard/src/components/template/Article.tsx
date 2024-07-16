import { Card, Collapse, Modal, Space } from "antd";
import { Typography } from "antd";
import { useState } from "react";
import Article, { ArticleType } from "../article/Article";
import { Link } from "react-router-dom";
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
      channelId={channel}
      parentChannels={parentChannels}
      focus={focus}
      mode="read"
      hideInteractive={true}
      hideTitle={true}
      isSubWindow
    />
  );
  let output = <></>;
  let articleLink = `/article/${type}/${id}?mode=read`;
  articleLink += channel ? `&channel=${channel}` : "";

  switch (style) {
    case "modal":
      output = (
        <>
          <Typography.Link
            onClick={(event: React.MouseEvent<HTMLElement, MouseEvent>) => {
              if (event.ctrlKey || event.metaKey) {
                let link = `/article/${type}/${id}?mode=read`;
                link += channel ? `&channel=${channel}` : "";
                window.open(fullUrl(link), "_blank");
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
                  <Link to={articleLink} target="_blank">
                    {intl.formatMessage({
                      id: "buttons.open.in.new.tab",
                    })}
                  </Link>
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
      output = <Card title={aTitle}>{article}</Card>;
      break;
    case "toggle":
      output = (
        <Collapse bordered={false}>
          <Collapse.Panel header={`${aTitle} ${anthology}`} key="parent2">
            {article}
          </Collapse.Panel>
        </Collapse>
      );
      break;
    case "link":
      let link = `/article/${type}/${id}?mode=read`;
      link += channel ? `&channel=${channel}` : "";
      output = (
        <Link to={link} target="_blank">
          {aTitle}
        </Link>
      );
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
