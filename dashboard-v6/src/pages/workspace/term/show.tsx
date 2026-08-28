import { useMatches, useNavigate, useParams, useSearchParams } from "react-router";
import { useIntl } from "react-intl";

import TermEditor from "../../../features/editor/Term";

const Widget = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const [searchParams, setSearchParams] = useSearchParams();
  const intl = useIntl();
  const matches = useMatches() as {
    data?: { title?: string; name?: string; word?: string };
  }[];
  const data = [...matches].reverse().find((m) => m.data)?.data;
  const name = data?.title ?? data?.name ?? data?.word;
  const prefix = intl.formatMessage({ id: "columns.studio.term.title" });
  const channelId = searchParams.get("channel");

  return (
    <>
      <title>{name ? `${prefix}-${name}` : prefix}</title>
      <TermEditor
        termId={id}
        channelId={channelId}
        onChannelSelect={(selected) => {
          const channelsParams = selected.map((item) => item.id).join("_");
          const newParams = new URLSearchParams(searchParams);
          newParams.set("channel", channelsParams);
          setSearchParams(newParams);
        }}
        onEdit={() => {
          navigate(`/workspace/term/${id}/edit`);
        }}
      />
    </>
  );
};

export default Widget;
