import { useParams } from "react-router-dom";

import AttachmentList from "../../../components/attachment/AttachmentList";

const Widget = () => {
  const { studioname } = useParams();
  return <AttachmentList studioName={studioname} />;
};

export default Widget;
