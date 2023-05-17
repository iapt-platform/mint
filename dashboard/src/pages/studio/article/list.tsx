import { useParams, useNavigate } from "react-router-dom";

import ArticleList from "../../../components/article/ArticleList";

const Widget = () => {
  const { studioname } = useParams(); //url 参数
  const navigate = useNavigate();

  return (
    <ArticleList
      studioName={studioname}
      editable={true}
      onSelect={(id: string) => {
        const url = `/studio/${studioname}/article/${id}/edit`;
        navigate(url);
      }}
    />
  );
};

export default Widget;
