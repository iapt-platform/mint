import { useParams } from "react-router";

import TermShow from "../../../components/term/TermShow";

const Widget = () => {
  const { id } = useParams();

  return <TermShow wordId={id} />;
};

export default Widget;
