import { useState } from "react";
import { useParams } from "react-router-dom";

import { Card, Space } from "antd";

import GoBack from "../../../components/studio/GoBack";
import ReadonlyLabel from "../../../components/general/ReadonlyLabel";

import ArticleEdit from "../../../components/article/ArticleEdit";
import ArticleEditTools from "../../../components/article/ArticleEditTools";

const Widget = () => {
  const { studioname, articleId } = useParams(); //url 参数
  const [title, setTitle] = useState("loading");
  const [readonly, setReadonly] = useState(false);

  return (
    <Card
      title={
        <Space>
          <GoBack to={`/studio/${studioname}/article/list`} title={title} />
          {readonly ? <ReadonlyLabel /> : undefined}
        </Space>
      }
      extra={
        <ArticleEditTools
          studioName={studioname}
          articleId={articleId}
          title={title}
        />
      }
    >
      <ArticleEdit
        articleId={articleId}
        onReady={(title: string, readonly: boolean) => {
          setTitle(title);
          setReadonly(readonly);
        }}
      />
    </Card>
  );
};

export default Widget;
