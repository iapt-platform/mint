import { useNavigate } from "react-router";
import { useIntl } from "react-intl";

import GroupList from "../../../features/group/GroupList";

const Widget = () => {
  const navigate = useNavigate();
  const intl = useIntl();
  return (
    <>
      <title>{intl.formatMessage({ id: "pages.team.title" })}</title>
      <GroupList
        onSelect={(id) => {
          const url = `/workspace/team/${id}`;
          navigate(url);
        }}
        onSetting={(id) => {
          const url = `/workspace/team/${id}/setting`;
          navigate(url);
        }}
      />
    </>
  );
};

export default Widget;
