import { useNavigate, useParams } from "react-router";

import TermEdit from "../../../components/term/TermEdit";

const Widget = () => {
  const { id } = useParams();
  const navigate = useNavigate();

  return (
    <TermEdit
      id={id}
      onUpdate={() => {
        navigate(`/workspace/term/${id}`);
      }}
    />
  );
};

export default Widget;
