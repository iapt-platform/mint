import { useNavigate, useParams } from "react-router-dom";

import AnthologyList from "../../../components/anthology/AnthologyList";

const Widget = () => {
  const { studioname } = useParams();
  const navigate = useNavigate();

  return (
    <AnthologyList
      studioName={studioname}
      onTitleClick={(id: string) => {
        navigate(`/studio/${studioname}/anthology/${id}/edit`);
      }}
    />
  );
};

export default Widget;
