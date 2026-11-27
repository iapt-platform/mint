import { useCallback, useState } from "react";

/**
 * 页面级数据加载失败的统一接法。
 *
 * pro-components 的 `request` 失败时：ProForm 把异常吞掉、照样渲染空表单；ProTable / ProList
 * 停在空数据上显示「暂无数据」。看起来都像「加载成功但没有内容」。用这个 hook 把失败接住，
 * 组件改为渲染 `<ApiErrorResult error={load.error} onRetry={load.retry} />`，不渲染表单或表格。
 *
 * ```tsx
 * const load = useLoadGuard();
 * if (load.error) return <ApiErrorResult error={load.error} onRetry={load.retry} />;
 * <ProTable key={load.attempt} onRequestError={load.onError} request={...} />
 * <ProForm key={load.attempt} request={load.guard(async () => ...)} />
 * <useEffect>  fetchX().then(set, load.onError)  // 依赖里放 load.attempt
 * ```
 *
 * 「重试」清掉错误并让 `attempt` 加一：把它放进 `key` 让组件重新挂载（重新请求），
 * 或放进 useEffect 的依赖。
 */
export const useLoadGuard = () => {
  const [error, setError] = useState<unknown>();
  const [attempt, setAttempt] = useState(0);

  const onError = useCallback((e: unknown) => setError(e), []);

  /** 包一层 ProForm 的 request：失败时记下错误再抛出 */
  const guard = useCallback(
    <A extends unknown[], R>(fn: (...args: A) => Promise<R>) =>
      async (...args: A): Promise<R> => {
        try {
          return await fn(...args);
        } catch (e) {
          setError(e);
          throw e;
        }
      },
    [],
  );

  const retry = useCallback(() => {
    setError(undefined);
    setAttempt((n) => n + 1);
  }, []);

  return { error, attempt, onError, guard, retry };
};
