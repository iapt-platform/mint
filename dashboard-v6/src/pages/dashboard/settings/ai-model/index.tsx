import { useIntl } from "react-intl";
import AiModelList from "../../../../components/ai-model/AiModelList";
import { useAppSelector } from "../../../../hooks";
import { currentUser } from "../../../../reducers/current-user";

const Widget = () => {
  const currUser = useAppSelector(currentUser);
  const intl = useIntl();

  return (
    <>
      <title>{intl.formatMessage({ id: "buttons.ai-models" })}</title>
      <AiModelList studioName={currUser?.realName} />
    </>
  );
};

export default Widget;
