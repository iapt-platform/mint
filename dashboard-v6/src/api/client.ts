// src/api/client.ts
//
// 共享的类型化 API 客户端。v3 的新调用一律走这里，v2 的老调用继续用 src/request.ts。
//
// 与手写 URL 相比，它把「路径字面量」和「HTTP 方法」也纳入了类型检查：
// 路径拼错、对只有 GET 的端点用 POST、路径参数名写错，都会在编译期报错。

import createClient, { type Middleware } from "openapi-fetch";

import { get as getToken } from "../reducers/session";
import { ApiError, reportApiError } from "./error";
import type { paths } from "./schema";

/** 每次请求注入 Bearer token，等价于 request.ts 里 options() 做的事 */
const authMiddleware: Middleware = {
  onRequest({ request }) {
    const token = getToken();
    if (token) {
      request.headers.set("Authorization", `Bearer ${token}`);
    }
    return request;
  },
};

/**
 * baseUrl 是 "/api"，所以调用时写的是 OpenAPI 规格里的 path（如 "/v3/heartbeat"），
 * 最终请求 "/api/v3/heartbeat"。开发期由 vite proxy 转发到后端。
 */
export const api = createClient<paths>({
  baseUrl: "/api",
  credentials: "include",
  mode: "cors",
});

api.use(authMiddleware);

/** openapi-fetch 的返回值：非 2xx 时不抛异常，而是把错误放进 error */
type FetchResult<T> = { data?: T; error?: unknown; response: Response };

/** RFC 9457 的错误体 */
type ProblemDetails = {
  detail?: string;
  title?: string;
  errors?: Record<string, string[]>;
};

const toApiError = (result: FetchResult<unknown>): ApiError => {
  const problem = result.error as ProblemDetails | undefined;
  // 422 的字段级错误一并带上，表单可以直接用
  return new ApiError(
    result.response.status,
    problem?.detail ?? problem?.title,
    problem?.errors,
  );
};

/**
 * 取出数据；失败则**弹出提示并抛出**。
 *
 * 于是调用点之后的代码保证是成功路径，既不用判断 `ok` 字段（v2 那套在全仓库
 * 重复了 221 处），也不用写 try/catch 去弹窗——异常由 error.ts 的全局兜底收尾。
 *
 * 需要拿到字段级错误时（表单），再自己 catch 并用 `fieldErrorsOf(e)`。
 */
export const unwrap = <T>(result: FetchResult<T>): T => {
  if (result.error !== undefined || result.data === undefined) {
    const error = toApiError(result);
    reportApiError(error);
    throw error;
  }
  return result.data;
};

/**
 * 同 unwrap，但**不弹提示**，只抛异常。
 *
 * 给那些自己渲染失败状态的调用用——例如心跳轮询：网络一断就每隔几秒弹一次窗，
 * 比不弹更糟。见 `api-health.ts`。
 */
export const unwrapQuiet = <T>(result: FetchResult<T>): T => {
  if (result.error !== undefined || result.data === undefined) {
    throw toApiError(result);
  }
  return result.data;
};

/**
 * 给 **204 No Content** 的端点用：只判成败，不取 body。
 *
 * v3 的 destroy 一律返回 204 空体（见 v3-resource skill 硬规范第 2 条）。
 * 而 `openapi-fetch` 对 204 返回的是 `{ data: undefined }`——`unwrap()` 见到
 * `data === undefined` 会当成失败、弹提示再抛异常，明明请求是成功的。
 * 所以 204 端点必须走这里，不能走 `unwrap()`。
 *
 * 失败时的行为与 `unwrap()` 一致：弹提示并抛出。
 */
export const unwrapVoid = (result: FetchResult<unknown>): void => {
  if (result.response.ok) {
    return;
  }
  const error = toApiError(result);
  reportApiError(error);
  throw error;
};
