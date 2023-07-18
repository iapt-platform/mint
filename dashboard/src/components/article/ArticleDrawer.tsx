import { Drawer } from "antd";
import React, { useEffect, useState } from "react";

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
