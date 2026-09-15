import { useMatches, useNavigate, useParams, useSearchParams } from "react-router";
import { useIntl } from "react-intl";

import AnthologyReader from "../../../components/anthology/AnthologyReader";

const Widget = () => {
  const { anthologyId } = useParams(); //url 参数
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const intl = useIntl();
  const matches = useMatches() as {
    data?: { title?: string; name?: string; word?: string };
  }[];
  const data = [...matches].reverse().find((m) => m.data)?.data;
  const name = data?.title ?? data?.name ?? data?.word;
  const prefix = intl.formatMessage({ id: "columns.studio.anthology.title" });

  const channelId = searchParams.get("channel");
  const channels = channelId ? channelId.split("_") : undefined;

  return (
    <>
      <title>{name ? `${prefix}-${name}` : prefix}</title>
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
