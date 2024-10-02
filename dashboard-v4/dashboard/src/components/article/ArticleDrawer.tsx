import { Button, Drawer, Space, Typography } from "antd";
import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";

import Article, { ArticleMode, ArticleType } from "./Article";
import { IArticleDataResponse } from "../api/Article";
const { Text } = Typography;

interface IWidget {
  trigger?: React.ReactNode;
  title?: string;
  type?: ArticleType;
  book?: string;
  para?: string;
  channelId?: string;
  articleId?: string;
  anthologyId?: string;
  mode?: ArticleMode;
  open?: boolean;
  onClose?: Function;
  onTitleChange?: Function;
  onArticleEdit?: Function;
}

const ArticleDrawerWidget = ({
  trigger,
  title,
  type,
  book,
  para,
  channelId,
  articleId,
  anthologyId,
  mode,
  open,
  onClose,
  onTitleChange,
  onArticleEdit,
}: IWidget) => {
  const [openDrawer, setOpenDrawer] = useState(open);
  const [drawerTitle, setDrawerTitle] = useState(title);
  useEffect(() => setOpenDrawer(open), [open]);
  useEffect(() => setDrawerTitle(title), [title]);
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
        title={
          <Text
            editable={{
              onChange: (value: string) => {
                setDrawerTitle(value);
                if (typeof onTitleChange !== "undefined") {
                  onTitleChange(value);
                }
              },
            }}
          >
            {drawerTitle}
          </Text>
        }
        width={1000}
        placement="right"
        onClose={onDrawerClose}
        open={openDrawer}
        destroyOnClose={true}
        extra={
          <Space>
            <Button>
              <Link to={getUrl()}>在单页面中打开</Link>
            </Button>
            <Button>
              <Link to={getUrl("edit")}>翻译模式</Link>
            </Button>
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
          anthologyId={anthologyId}
          mode={mode}
          onArticleEdit={(value: IArticleDataResponse) => {
            setDrawerTitle(value.title_text);
            if (typeof onArticleEdit !== "undefined") {
              onArticleEdit(value);
            }
          }}
        />
      </Drawer>
    </>
  );
};

export default ArticleDrawerWidget;
