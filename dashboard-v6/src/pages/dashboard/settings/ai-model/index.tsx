import AiModelList from "../../../../components/ai-model/AiModelList";
import { useAppSelector } from "../../../../hooks";
import { currentUser } from "../../../../reducers/current-user";

const Widget = () => {
  const currUser = useAppSelector(currentUser);

  return <AiModelList studioName={currUser?.realName} />;
};

export default Widget;
