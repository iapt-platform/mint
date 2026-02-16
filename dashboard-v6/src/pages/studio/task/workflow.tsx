import { useParams } from "react-router-dom";

import Workflow from "../../../components/task/Workflow";

const Widget = () => {
  const { studioname } = useParams();

  return <Workflow studioName={studioname} />;
};

export default Widget;
