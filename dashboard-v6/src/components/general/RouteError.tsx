import { Button, Result } from "antd";
import { useIntl } from "react-intl";
import {
  isRouteErrorResponse,
  useNavigate,
  useRevalidator,
  useRouteError,
} from "react-router";

import { ApiError } from "../../api/error";
import ApiErrorResult from "./ApiErrorResult";

interface IWidget {
  /** 「返回」按钮去哪里 */
  backTo?: string;
}

/**
 * 路由数据加载失败时的错误页（loader 抛出的 404 / 403 等），代替 React Router 默认的
 * 「Unexpected Application Error」。挂在路由的 errorElement 上。
 *
 * loader 原样抛出 v3 的 ApiError 时（超时、网络不通、5xx）按 ApiErrorResult 显示，带「重试」：
 * 重新跑 loader，成功就回到正常页面。
 */
const RouteError = ({ backTo = "/workspace" }: IWidget) => {
  const intl = useIntl();
  const navigate = useNavigate();
  const revalidator = useRevalidator();
  const error = useRouteError();

  const back = (
    <Button onClick={() => navigate(backTo)}>
      {intl.formatMessage({ id: "buttons.back" })}
    </Button>
  );

  if (error instanceof ApiError) {
    return (
      <ApiErrorResult
        error={error}
        onRetry={() => revalidator.revalidate()}
        extra={back}
      />
    );
  }

  const status = isRouteErrorResponse(error) ? error.status : 500;
  const known = status === 403 || status === 404;

  return (
    <Result
      status={known ? (String(status) as "403" | "404") : "error"}
      title={intl.formatMessage({
        id: known ? `labels.route-error.${status}` : "labels.route-error.other",
      })}
      extra={
        <Button type="primary" onClick={() => navigate(backTo)}>
          {intl.formatMessage({ id: "buttons.back" })}
        </Button>
      }
    />
  );
};

export default RouteError;
