import { useParams } from "react-router";
import { useIntl } from "react-intl";
import AiModelEdit from "../../../../components/ai-model/AiModelEdit";

import { useAppSelector } from "../../../../hooks";
import { currentUser } from "../../../../reducers/current-user";

const Widget = () => {
  const currUser = useAppSelector(currentUser);
  const { id } = useParams();
  const intl = useIntl();

  return (
    <>
      <title>{intl.formatMessage({ id: "pages.ai-model.edit.title" })}</title>
      <AiModelEdit studioName={currUser?.realName} modelId={id} />
    </>
  );
};

export default Widget;
