import { useState } from "react";
import { useParams } from "react-router-dom";
import ArticleEditDrawer from "../../../components/article/ArticleEditDrawer";

import ArticleList from "../../../components/article/ArticleList";

const Widget = () => {
  const { studioname } = useParams(); //url 参数
  const [articleId, setArticleId] = useState<string>();
  const [open, setOpen] = useState<boolean>(false);

  return (
    <>
      <ArticleList
        studioName={studioname}
        editable={true}
        onSelect={(id: string) => {
          setArticleId(id);
          setOpen(true);
          //const url = `/studio/${studioname}/article/${id}/edit`;
          //navigate(url);
        }}
      />
      <ArticleEditDrawer
        articleId={articleId}
        open={open}
        onClose={() => setOpen(false)}
      />
    </>
  );
};

export default Widget;
