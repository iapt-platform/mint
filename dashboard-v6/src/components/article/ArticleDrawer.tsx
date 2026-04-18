import { Button, Drawer, Space, Typography } from "antd";
import React, { useEffect, useState } from "react";
import { Link } from "react-router";

import type {
  ArticleMode,
  ArticleType,
  IArticleDataResponse,
} from "../../api/article";
import TypeArticle from "./TypeArticle";
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
  onClose?: () => void;
  onTitleChange?: (value: string) => void;
  onArticleEdit?: (value: IArticleDataResponse) => void;
}

const ArticleDrawer = ({
  trigger,
  title,
  type,
  channelId,
  articleId,
  mode,
  open,
  onClose,
  onTitleChange,
}: IWidget) => {
  const [openDrawer, setOpenDrawer] = useState(open);
  const [drawerTitle, setDrawerTitle] = useState(title);
  useEffect(() => {
    setOpenDrawer(open);
  }, [open]);

  useEffect(() => {
    setDrawerTitle(title);
  }, [title]);

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
    let url = `/workspace/${type}/${articleId}?mode=`;
    url += openMode ? openMode : mode ? mode : "read";
    url += channelId ? `&channel=${channelId}` : "";
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
        size={1000}
        placement="right"
        onClose={onDrawerClose}
        open={openDrawer}
        destroyOnHidden={true}
        extra={
          <Space>
            <Button>
              <Link to={getUrl()} target="_blank">
                在新标签页打开
              </Link>
            </Button>
          </Space>
        }
      >
        <TypeArticle articleId={articleId} />
      </Drawer>
    </>
  );
};

export default ArticleDrawer;
