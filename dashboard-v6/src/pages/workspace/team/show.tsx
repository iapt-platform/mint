import { useMatches, useNavigate, useParams } from "react-router";
import { useIntl } from "react-intl";

import GroupShow from "../../../features/group/GroupShow";

const Widget = () => {
  const { teamId } = useParams(); //url 参数
  const navigate = useNavigate();
  const intl = useIntl();
  const matches = useMatches() as {
    data?: { title?: string; name?: string; word?: string };
  }[];
  const data = [...matches].reverse().find((m) => m.data)?.data;
  const name = data?.title ?? data?.name ?? data?.word;
  const prefix = intl.formatMessage({ id: "pages.team.title" });

  return (
    <>
      <title>{name ? `${prefix}-${name}` : prefix}</title>
      <GroupShow
        teamId={teamId}
        onSetting={() => navigate(`/workspace/team/${teamId}/setting`)}
      />
    </>
  );
};

export default Widget;
