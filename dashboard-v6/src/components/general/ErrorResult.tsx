import { Result } from "antd";
import { ResultStatusType } from "antd/lib/result";
import { useIntl } from "react-intl";

interface IWidget {
  code: number;
  message?: string;
}

const ErrorResultWidget = ({ code, message }: IWidget) => {
  const intl = useIntl();
  let strStatus: ResultStatusType;
  let strTitle: string = "";
  switch (code) {
    case 401:
      strStatus = 403;
      strTitle = intl.formatMessage({ id: "labels.error.401" });
      break;
    case 403:
      strStatus = 403;
      strTitle = intl.formatMessage({ id: "labels.error.403" });
      break;
    case 404:
      strStatus = 404;
      strTitle = intl.formatMessage({ id: "labels.error.404" });
      break;
    case 500:
      strStatus = 500;
      strTitle = intl.formatMessage({ id: "labels.error.500" });
      break;
    case 429:
      strStatus = "error";
      strTitle = intl.formatMessage({ id: "labels.error.429" });
      break;
    default:
      strStatus = "error";
      strTitle = "无法识别的错误代码" + code;
      break;
  }
  return (
    <Result
      status={strStatus}
      title={strTitle}
      subTitle={message ? message : "Sorry, something went wrong."}
    />
  );
};

export default ErrorResultWidget;
