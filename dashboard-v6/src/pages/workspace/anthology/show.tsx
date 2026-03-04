import { useNavigate, useParams, useSearchParams } from "react-router";

import AnthologyDetail from "../../../components/anthology/AnthologyDetail";

const Widget = () => {
  const { id } = useParams(); //url 参数
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();

  const channelId = searchParams.get("channel");
  const channels = channelId ? channelId.split("_") : undefined;

  return (
    <>
      <title>{"anthology-"}</title>
      <AnthologyDetail
        channels={channels}
        id={id}
        onArticleClick={(anthologyId, articleId, target) => {
          console.log("click", target);
          navigate(`/workspace/article/${articleId}?anthology=${anthologyId}`);
        }}
      />
    </>
  );
};

export default Widget;
