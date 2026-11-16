import { Button, Result } from "antd";
import { useIntl } from "react-intl";
import { isRouteErrorResponse, useNavigate, useRouteError } from "react-router";

interface IWidget {
  /** 「返回」按钮去哪里 */
  backTo?: string;
}

/**
 * 路由数据加载失败时的错误页（loader 抛出的 404 / 403 等），代替 React Router 默认的
 * 「Unexpected Application Error」。挂在路由的 errorElement 上。
 */
const RouteError = ({ backTo = "/workspace" }: IWidget) => {
  const intl = useIntl();
  const navigate = useNavigate();
  const error = useRouteError();
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
