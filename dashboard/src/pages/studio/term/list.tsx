import { useParams } from "react-router-dom";
import TermList from "../../../components/term/TermList";

const Widget = () => {
  const { studioname } = useParams();

  return <TermList studioName={studioname} />;
};

export default Widget;
