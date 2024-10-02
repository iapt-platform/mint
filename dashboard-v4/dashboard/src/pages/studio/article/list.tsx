import { useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import ArticleEditDrawer from "../../../components/article/ArticleEditDrawer";

import ArticleList from "../../../components/article/ArticleList";
import { fullUrl } from "../../../utils";

const Widget = () => {
  const { studioname } = useParams(); //url 参数
  const [articleId, setArticleId] = useState<string>();
  const [open, setOpen] = useState<boolean>(false);
  const navigate = useNavigate();

  return (
    <>
      <ArticleList
        studioName={studioname}
        editable={true}
        onSelect={(
          id: string,
          title: string,
          event: React.MouseEvent<HTMLElement, MouseEvent>
        ) => {
          setArticleId(id);
          const url = `/studio/${studioname}/article/edit/${id}`;
          if (event.shiftKey) {
            navigate(url);
          } else if (event.ctrlKey || event.metaKey) {
            window.open(fullUrl(url), "_blank");
          } else {
            setOpen(true);
          }
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
