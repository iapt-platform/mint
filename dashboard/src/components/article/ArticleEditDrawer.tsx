import { Drawer } from "antd";
import React, { useEffect, useState } from "react";
import { IArticleDataResponse } from "../api/Article";

import ArticleEdit from "./ArticleEdit";

interface IWidget {
  trigger?: React.ReactNode;
  articleId?: string;
  anthologyId?: string;
  open?: boolean;
  onClose?: Function;
  onChange?: Function;
}

const ArticleEditDrawerWidget = ({
  trigger,
  articleId,
  anthologyId,
  open,
  onClose,
  onChange,
}: IWidget) => {
  const [openDrawer, setOpenDrawer] = useState(open);
  const [title, setTitle] = useState("loading");
  const [readonly, setReadonly] = useState(false);
  const [studioName, setStudioName] = useState<string>();

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
        title={title + (readonly ? "(只读)" : "")}
        width={1000}
        placement="right"
        onClose={onDrawerClose}
        open={openDrawer}
        destroyOnClose={true}
      >
        <ArticleEdit
          anthologyId={anthologyId}
          articleId={articleId}
          onReady={(title: string, readonly: boolean, studio?: string) => {
            setTitle(title);
            setReadonly(readonly);
            setStudioName(studio);
          }}
          onChange={(data: IArticleDataResponse) => {
            if (typeof onChange !== "undefined") {
              onChange(data);
            }
          }}
        />
      </Drawer>
    </>
  );
};

export default ArticleEditDrawerWidget;
