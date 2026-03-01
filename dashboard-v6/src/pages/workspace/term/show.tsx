import { useNavigate, useParams } from "react-router";

import TypeTerm from "../../../components/article/TypeTerm";

const Widget = () => {
  const { id } = useParams();
  const navigate = useNavigate();

  return (
    <TypeTerm
      id={id}
      onEdit={() => {
        navigate(`/workspace/edit/wiki/${id}/edit`);
      }}
    />
  );
};

export default Widget;
