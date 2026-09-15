import { useParams } from "react-router";
import { useIntl } from "react-intl";

import AiModelLogList from "../../../../components/ai-model/AiModelLogList";

const Widget = () => {
  const { id } = useParams();
  const intl = useIntl();

  return (
    <>
      <title>{intl.formatMessage({ id: "pages.ai-model.log.title" })}</title>
      <AiModelLogList modelId={id} />
    </>
  );
};

export default Widget;
