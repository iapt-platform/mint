import { useEffect, type ReactNode } from "react";
import { Button, Result } from "antd";
import { useIntl } from "react-intl";

import { ApiError, dismissApiError } from "../../api/error";

interface IWidget {
  /** 加载失败时 catch 到的东西，一般是 v3 的 ApiError */
  error: unknown;
  /** 给了就显示「重试」（401 / 403 / 404 重试也没用，不显示） */
  onRetry?: () => void;
  /** 额外的按钮，如「返回」 */
  extra?: ReactNode;
}

/**
 * v3 数据加载失败时的整页错误提示，代替表单 / 内容渲染。
 *
 * 超时、网络不通、无权限、不存在、其他错误分别给不同标题；副标题是错误的原文
 * （后端已本地化的 detail，或 client.ts 填的超时 / 网络文案）。
 * 挂载时收掉 unwrap() 为同一错误弹的提示，页面上只留这一处。
 */
const ApiErrorResult = ({ error, onRetry, extra }: IWidget) => {
  const intl = useIntl();
  const label = (id: string) => intl.formatMessage({ id });

  // 整页已经显示了这个错误，unwrap() 弹的同一条提示就多余了
  useEffect(() => {
    dismissApiError(error);
  }, [error]);

  const status = error instanceof ApiError ? error.status : 0;
  const kind = error instanceof ApiError ? error.kind : "http";
  const detail = error instanceof Error ? error.message : undefined;

  let resultStatus: "403" | "404" | "warning" | "error" = "error";
  let title = label("labels.route-error.other");
  let retryable = true;
  if (kind === "timeout" || kind === "network") {
    resultStatus = "warning";
    title = label(`labels.error.${kind}`);
  } else if (status === 401) {
    resultStatus = "403";
    title = label("labels.error.401");
    retryable = false;
  } else if (status === 403 || status === 404) {
    resultStatus = String(status) as "403" | "404";
    title = label(`labels.route-error.${status}`);
    retryable = false;
  }

  return (
    <Result
      status={resultStatus}
      title={title}
      // 标题已经说明了的就不重复
      subTitle={detail && detail !== title ? detail : undefined}
      extra={[
        retryable && onRetry ? (
          <Button key="retry" type="primary" onClick={onRetry}>
            {label("buttons.retry")}
          </Button>
        ) : null,
        extra ? <span key="extra">{extra}</span> : null,
      ]}
    />
  );
};

export default ApiErrorResult;
