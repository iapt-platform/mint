import { useNavigate } from "react-router";
import { useIntl } from "react-intl";

import TagList from "../../../components/tag/TagList";

import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const user = useAppSelector(currentUser);
  const studioName = user?.realName;
  const navigate = useNavigate();
  const intl = useIntl();
  return (
    <>
      <title>{intl.formatMessage({ id: "columns.studio.tag.title" })}</title>
      <TagList
        studioName={studioName}
        onSelect={(tag) => {
          const url = `/workspace/tag/${tag.id}`;
          navigate(url);
        }}
      />
    </>
  );
};

export default Widget;
