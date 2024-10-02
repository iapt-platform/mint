import { useNavigate, useParams } from "react-router-dom";

import TagList from "../../../components/tag/TagList";
import { ITagData } from "../../../components/api/Tag";
import TagShow from "../../../components/tag/TagShow";

const Widget = () => {
  const { studioname, id } = useParams();
  const navigate = useNavigate();
  return (
    <TagShow
      tagId={id}
      onSelect={(tag: ITagData) => {
        const url = `/studio/${studioname}/tags/${tag.id}/list`;
        navigate(url);
      }}
    />
  );
};

export default Widget;
