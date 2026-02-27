import { useParams } from "react-router";

import TypeTerm from "../../../components/article/TypeTerm";

const Widget = () => {
  const { id } = useParams();

  return <TypeTerm id={id} />;
};

export default Widget;
