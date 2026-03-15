import { useParams } from "react-router";
import AiModelEdit from "../../../../components/ai-model/AiModelEdit";

import { useAppSelector } from "../../../../hooks";
import { currentUser } from "../../../../reducers/current-user";

const Widget = () => {
  const currUser = useAppSelector(currentUser);
  const { id } = useParams();

  return <AiModelEdit studioName={currUser?.realName} modelId={id} />;
};

export default Widget;
