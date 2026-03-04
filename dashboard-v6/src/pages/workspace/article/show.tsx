import { useNavigate, useParams, useSearchParams } from "react-router";
import TypeArticle from "../../../components/article/TypeArticle";
import SplitLayout from "../../../components/general/SplitLayout";
import type { ArticleMode } from "../../../api/Article";

const Widget = () => {
  const { id } = useParams();
  const [searchParams, setSearchParams] = useSearchParams();
  const navigate = useNavigate();
  const mode = searchParams.get("mode") ?? "read";
  const channelId = searchParams.get("channel");
  const anthology = searchParams.get("anthology");

  return (
    <SplitLayout key="mode-a" sidebarTitle="table of content" sidebar={<></>}>
      {({ expandButton }) => (
        <TypeArticle
          articleId={id}
          mode={mode as ArticleMode}
          anthologyId={anthology}
          channelId={channelId}
          headerExtra={expandButton}
          onAnthologySelect={(id) => {
            setSearchParams((prev) => {
              const next = new URLSearchParams(prev);
              next.set("anthology", id);
              return next;
            });
          }}
          onArticleChange={(type, id) => {
            navigate(`/workspace/${type}/${id}`);
          }}
        />
      )}
    </SplitLayout>
  );
};

export default Widget;
