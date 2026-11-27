// src/api/client.ts
//
// 共享的类型化 API 客户端。v3 的新调用一律走这里，v2 的老调用继续用 src/request.ts。
//
// 与手写 URL 相比，它把「路径字面量」和「HTTP 方法」也纳入了类型检查：
// 路径拼错、对只有 GET 的端点用 POST、路径参数名写错，都会在编译期报错。

import createClient, { type Middleware } from "openapi-fetch";

import { get as getToken } from "../reducers/session";
import { get as getUiLang, messages } from "../locales";
import { ApiError, type ApiErrorKind, reportApiError } from "./error";
import type { paths } from "./schema";

/**
 * 每次请求注入 Bearer token（等价于 request.ts 里 options() 做的事），
 * 并用界面语言设置 Accept-Language。
 *
 * 后端 v3 按 Accept-Language 本地化错误文案（V3\NegotiateLocale）。浏览器自动带的
 * Accept-Language 是系统语言，不是用户在界面里选的语言，所以这里显式覆盖。
 */
const authMiddleware: Middleware = {
  onRequest({ request }) {
    const token = getToken();
    if (token) {
      request.headers.set("Authorization", `Bearer ${token}`);
    }
    request.headers.set("Accept-Language", getUiLang());
    return request;
  },
};

/** 普通请求的超时（从发出到响应体收完） */
const TIMEOUT_MS = 30_000;
/** 上传（multipart）与下载文件（不收 JSON）放宽 */
const FILE_TIMEOUT_MS = 120_000;

/** 前端合成的「请求没成功」响应带这个头，值是 ApiErrorKind（timeout / network） */
const TRANSPORT_ERROR_HEADER = "X-Client-Transport-Error";

/** 这几个状态码的响应不能带 body（new Response 会抛异常） */
const NULL_BODY_STATUS = new Set([101, 204, 205, 304]);

/** 界面语言的文案（非组件代码里取不到 useIntl） */
const text = (id: string): string =>
  (messages(getUiLang()) as Record<string, string>)[id] ?? id;

const isFileTransfer = (request: Request): boolean =>
  (request.headers.get("Content-Type") ?? "").startsWith("multipart/") ||
  !(request.headers.get("Accept") ?? "application/json").includes("json");

/**
 * 所有 v3 请求经过这里：
 *
 * - **超时**：从发出到响应体收完，超过 TIMEOUT_MS 就中止。浏览器的 fetch 本身没有超时，
 *   后端不回就会一直等下去
 * - **超时 / 网络不通不抛异常**：openapi-fetch 遇到 fetch 抛异常会原样抛出，绕过
 *   `unwrap()`——不弹提示、也不是 ApiError，调用方无从区分。这里把它们换成一个合成的
 *   失败响应（带 TRANSPORT_ERROR_HEADER），于是和 4xx / 5xx 走同一条路：
 *   `unwrap()` 弹提示并抛 ApiError，`unwrapQuiet()` 只抛
 * - **调用方自己取消**（传了 signal 并 abort）照旧抛 AbortError，由调用方忽略
 *
 * 响应体在这里读完再交出去，超时才能覆盖「响应头到了、正文迟迟不来」的情况。
 */
const fetchWithTimeout = async (request: Request): Promise<Response> => {
  const timeout = AbortSignal.timeout(
    isFileTransfer(request) ? FILE_TIMEOUT_MS : TIMEOUT_MS,
  );
  try {
    const response = await fetch(request, {
      signal: AbortSignal.any([request.signal, timeout]),
    });
    const body = NULL_BODY_STATUS.has(response.status)
      ? null
      : await response.arrayBuffer();
    // body 已解压，原来的长度与编码头不再对得上
    const headers = new Headers(response.headers);
    headers.delete("Content-Length");
    headers.delete("Content-Encoding");
    return new Response(body, {
      status: response.status,
      statusText: response.statusText,
      headers,
    });
  } catch (e) {
    if (request.signal.aborted) {
      throw e;
    }
    const kind: ApiErrorKind = timeout.aborted ? "timeout" : "network";
    return new Response(
      JSON.stringify({ title: text(`labels.error.${kind}`) }),
      {
        // 状态码只是占位（Response 不接受 0），toApiError 认的是 TRANSPORT_ERROR_HEADER
        status: kind === "timeout" ? 504 : 503,
        headers: {
          "Content-Type": "application/problem+json",
          [TRANSPORT_ERROR_HEADER]: kind,
        },
      },
    );
  }
};

/**
 * baseUrl 是 "/api"，所以调用时写的是 OpenAPI 规格里的 path（如 "/v3/heartbeat"），
 * 最终请求 "/api/v3/heartbeat"。开发期由 vite proxy 转发到后端。
 */
export const api = createClient<paths>({
  baseUrl: "/api",
  credentials: "include",
  mode: "cors",
  fetch: fetchWithTimeout,
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
  const { response } = result;
  const transport = response.headers.get(TRANSPORT_ERROR_HEADER);
  if (transport === "timeout" || transport === "network") {
    return new ApiError(
      0,
      text(`labels.error.${transport}`),
      undefined,
      transport,
    );
  }
  // 网关回的 502 / 504 等，响应体是 HTML（openapi-fetch 给的是字符串），没有 detail
  const problem =
    typeof result.error === "object"
      ? (result.error as ProblemDetails | null)
      : undefined;
  const kind: ApiErrorKind =
    response.status === 408 || response.status === 504 ? "timeout" : "http";
  // 422 的字段级错误一并带上，表单可以直接用
  return new ApiError(
    response.status,
    problem?.detail ??
      problem?.title ??
      (kind === "timeout" ? text("labels.error.timeout") : undefined),
    problem?.errors,
    kind,
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
