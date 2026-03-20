import { useNavigate } from "react-router";

import TagList from "../../../components/tag/TagList";

import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const user = useAppSelector(currentUser);
  const studioName = user?.realName;
  const navigate = useNavigate();
  return (
    <TagList
      studioName={studioName}
      onSelect={(tag) => {
        const url = `/workspace/tag/${tag.id}`;
        navigate(url);
      }}
    />
  );
};

export default Widget;
