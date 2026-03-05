import { useNavigate, useParams, useSearchParams } from "react-router";

import AnthologyReader from "../../../components/anthology/AnthologyReader";

const Widget = () => {
  const { anthologyId } = useParams(); //url 参数
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();

  const channelId = searchParams.get("channel");
  const channels = channelId ? channelId.split("_") : undefined;

  return (
    <>
      <title>{"anthology-"}</title>
      <AnthologyReader
        channels={channels}
        id={anthologyId}
        onArticleClick={(anthologyId, articleId, target) => {
          console.log("click", target);
          navigate(`/workspace/anthology/${anthologyId}/${articleId}`);
        }}
        onEdit={() => {
          navigate(`/workspace/anthology/${anthologyId}/edit`);
        }}
      />
    </>
  );
};

export default Widget;
