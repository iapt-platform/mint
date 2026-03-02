import { useParams } from "react-router";

import ChapterPage from "../../../features/tipitaka/ChapterPage";

const Widget = () => {
  const { id } = useParams();
  console.log("chapter", id);
  return <ChapterPage id={id} />;
};

export default Widget;
