import { Button, Drawer, Space } from "antd";
import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";

import Article, { ArticleMode, ArticleType } from "./Article";

interface IWidget {
  trigger?: React.ReactNode;
  title?: string;
  type?: ArticleType;
  book?: string;
  para?: string;
  channelId?: string;
  articleId?: string;
  mode?: ArticleMode;
  open?: boolean;
  onClose?: Function;
}

const ArticleDrawerWidget = ({
  trigger,
  title,
  type,
  book,
  para,
  channelId,
  articleId,
  mode,
  open,
  onClose,
}: IWidget) => {
  const [openDrawer, setOpenDrawer] = useState(open);
  useEffect(() => setOpenDrawer(open), [open]);
  const showDrawer = () => {
    setOpenDrawer(true);
  };

  const onDrawerClose = () => {
    setOpenDrawer(false);
    if (document.getElementsByTagName("body")[0].hasAttribute("style")) {
      document.getElementsByTagName("body")[0].removeAttribute("style");
    }
    if (typeof onClose !== "undefined") {
      onClose();
    }
  };

  const getUrl = (openMode?: string): string => {
    let url = `/article/${type}/${articleId}?mode=`;
    url += openMode ? openMode : mode ? mode : "read";
    url += channelId ? `&channel=${channelId}` : "";
    url += book ? `&book=${book}` : "";
    url += para ? `&par=${para}` : "";
    return url;
  };

  return (
    <>
      <span onClick={() => showDrawer()}>{trigger}</span>
      <Drawer
        title={title}
        width={1000}
        placement="right"
        onClose={onDrawerClose}
        open={openDrawer}
        destroyOnClose={true}
        extra={
          <Space>
            <Link to={getUrl()}>在单页面中打开</Link>
            <Link to={getUrl("edit")}>翻译模式</Link>
          </Space>
        }
      >
        <Article
          active={true}
          type={type as ArticleType}
          book={book}
          para={para}
          channelId={channelId}
          articleId={articleId}
          mode={mode}
        />
      </Drawer>
    </>
  );
};

export default ArticleDrawerWidget;
