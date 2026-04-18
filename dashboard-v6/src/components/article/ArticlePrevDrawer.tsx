import { Drawer, Typography } from "antd";
import React, { useState } from "react";
import { put } from "../../request";
import type { IArticleDataResponse, IArticleResponse } from "../../api/article";
import ArticleLayout from "./components/ArticleLayout";

const { Paragraph } = Typography;

interface IArticlePrevRequest {
  content: string;
}
interface IWidget {
  trigger?: React.ReactNode;
  title?: React.ReactNode;
  content?: string;
  articleId: string;
}

const ArticlePrevDrawer = ({ trigger, title, content, articleId }: IWidget) => {
  const [articleData, setArticleData] = useState<IArticleDataResponse>();
  const [open, setOpen] = useState(false);
  const [errorMsg, setErrorMsg] = useState<string>();

  const showDrawer = () => {
    console.info("ArticlePrevDrawer save");
    put<IArticlePrevRequest, IArticleResponse>(
      `/api/v2/article-preview/${articleId}`,
      {
        content: content ? content : "",
      }
    )
      .then((res) => {
        console.log("save response", res);
        if (res.ok) {
          setArticleData(res.data);
        } else {
          setErrorMsg(res.message);
        }
      })
      .catch((e: IArticleResponse) => {
        setErrorMsg(e.message);
      });
    setOpen(true);
  };

  const onClose = () => {
    setOpen(false);
  };

  return (
    <>
      <span onClick={() => showDrawer()}>{trigger}</span>
      <Drawer
        title={title}
        width={900}
        placement="right"
        onClose={onClose}
        open={open}
        destroyOnHidden={true}
      >
        <Paragraph type="danger">{errorMsg}</Paragraph>
        {articleData ? (
          <ArticleLayout
            title={articleData.title}
            subTitle={articleData.subtitle}
            summary={articleData.summary}
            content={articleData.content ? articleData.content : ""}
            html={articleData.html ? [articleData.html] : []}
            created_at={articleData.created_at}
            updated_at={articleData.updated_at}
          />
        ) : (
          <></>
        )}
      </Drawer>
    </>
  );
};

export default ArticlePrevDrawer;
