import { useNavigate, useParams, useSearchParams } from "react-router";
import TypeArticle from "../../../components/article/TypeArticle";
import SplitLayout from "../../../components/general/SplitLayout";
import type { ArticleMode } from "../../../api/Article";
import AnthologyTocTree from "../../../components/anthology/AnthologyTocTree";

const Widget = () => {
  const { articleId, anthologyId } = useParams();
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const mode = searchParams.get("mode") ?? "read";
  const channelId = searchParams.get("channel");
  const anthology = searchParams.get("anthology");

  return (
    <SplitLayout
      key="mode-a"
      sidebarTitle="table of content"
      sidebar={
        anthologyId ? (
          <AnthologyTocTree
            anthologyId={anthologyId}
            channels={channelId ? channelId.split("_") : undefined}
            onClick={(anthology, article, target) => {
              if (target && target === "_blank") {
                window.open(
                  `${window.location.origin}${import.meta.env.BASE_URL}workspace/anthology/${anthology}/${article}`,
                  "_blank"
                );
              } else {
                navigate(`/workspace/anthology/${anthology}/${article}`);
              }
            }}
          />
        ) : (
          <></>
        )
      }
    >
      {({ expandButton }) => (
        <TypeArticle
          articleId={articleId}
          mode={mode as ArticleMode}
          anthologyId={anthologyId ?? anthology}
          channelId={channelId}
          headerExtra={expandButton}
          onAnthologySelect={(id) => {
            navigate(`/workspace/anthology/${id}/${articleId}`);
          }}
          onArticleChange={(type, id) => {
            if (anthologyId) {
              if (type === "article") {
                navigate(`/workspace/anthology/${anthologyId}/${id}`);
              } else {
                navigate(`/workspace/${type}/${id}`);
              }
            } else {
              navigate(`/workspace/${type}/${id}`);
            }
          }}
        />
      )}
    </SplitLayout>
  );
};

export default Widget;
