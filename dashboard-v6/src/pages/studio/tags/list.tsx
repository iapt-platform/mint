import { useNavigate, useParams } from "react-router-dom";

import TagList from "../../../components/tag/TagList";
import { ITagData } from "../../../components/api/Tag";

const Widget = () => {
  const { studioname } = useParams();
  const navigate = useNavigate();
  return (
    <TagList
      studioName={studioname}
      onSelect={(tag: ITagData) => {
        const url = `/studio/${studioname}/tags/${tag.id}/list`;
        navigate(url);
      }}
    />
  );
};

export default Widget;
