import { Drawer, Typography } from "antd";
import React, { useEffect, useState } from "react";
import { put } from "../../request";
import { IArticleDataResponse, IArticleResponse } from "../api/Article";
import ArticleView from "./ArticleView";

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

const ArticlePrevDrawerWidget = ({
  trigger,
  title,
  content,
  articleId,
}: IWidget) => {
  const [articleData, setArticleData] = useState<IArticleDataResponse>();
  const [open, setOpen] = useState(false);
  const [errorMsg, setErrorMsg] = useState<string>();

  const showDrawer = () => {
    setOpen(true);
  };

  const onClose = () => {
    setOpen(false);
  };

  useEffect(() => {
    put<IArticlePrevRequest, IArticleResponse>(
      `/v2/article-preview/${articleId}`,
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
  }, [articleId, content]);

  return (
    <>
      <span onClick={() => showDrawer()}>{trigger}</span>
      <Drawer
        title={title}
        width={900}
        placement="right"
        onClose={onClose}
        open={open}
        destroyOnClose={true}
      >
        <Paragraph type="danger">{errorMsg}</Paragraph>
        {articleData ? (
          <ArticleView
            id={articleData.uid}
            title={articleData.title}
            subTitle={articleData.subtitle}
            summary={articleData.summary}
            content={articleData.content ? articleData.content : ""}
            html={articleData.html ? [articleData.html] : []}
            path={articleData.path}
            created_at={articleData.created_at}
            updated_at={articleData.updated_at}
            articleId={articleId}
          />
        ) : (
          <></>
        )}
      </Drawer>
    </>
  );
};

export default ArticlePrevDrawerWidget;
