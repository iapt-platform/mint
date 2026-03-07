import { useNavigate, useParams } from "react-router";

import TermEditor from "../../../features/editor/Term";

const Widget = () => {
  const { id } = useParams();
  const navigate = useNavigate();

  return (
    <TermEditor
      termId={id}
      onEdit={() => {
        navigate(`/workspace/term/${id}/edit`);
      }}
    />
  );
};

export default Widget;
