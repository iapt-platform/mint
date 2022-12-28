import { useParams } from "react-router-dom";
import { useIntl } from "react-intl";

const Widget = () => {
  const intl = useIntl(); //i18n
  const { studioname } = useParams(); //url 参数
  return <>{studioname}</>;
};

export default Widget;
