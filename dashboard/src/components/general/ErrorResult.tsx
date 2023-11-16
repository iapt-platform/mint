import { Result } from "antd";
import { ResultStatusType } from "antd/lib/result";

interface IWidget {
  code: number;
  message?: string;
}

const ErrorResultWidget = ({ code, message }: IWidget) => {
  let strStatus: ResultStatusType;
  let strTitle: string = "";
  switch (code) {
    case 401:
      strStatus = 403;
      strTitle = "未登录";
      break;
    case 403:
      strStatus = 403;
      strTitle = "没有权限";
      break;
    case 404:
      strStatus = 404;
      strTitle = "没有找到指定的资源";
      break;
    case 500:
      strStatus = 500;
      strTitle = "服务器内部错误";
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
      subTitle="Sorry, something went wrong."
    />
  );
};

export default ErrorResultWidget;
