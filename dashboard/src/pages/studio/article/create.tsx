import { useNavigate, useParams, useSearchParams } from "react-router-dom";

import { Card, Space } from "antd";

import GoBack from "../../../components/studio/GoBack";

import ArticleCreate from "../../../components/article/ArticleCreate";
import { IArticleDataResponse } from "../../../components/api/Article";

const Widget = () => {
  const { studioname } = useParams(); //url 参数
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();

  return (
    <Card
      title={
        <Space>
          <GoBack to={`/studio/${studioname}/article/list`} title={"新建"} />
        </Space>
      }
    >
      <ArticleCreate
        studio={studioname}
        parentId={searchParams.get("parent")}
        compact={false}
        onSuccess={(article: IArticleDataResponse) => {
          navigate(`/studio/${studioname}/article/edit/${article.uid}`);
        }}
      />
    </Card>
  );
};

export default Widget;
