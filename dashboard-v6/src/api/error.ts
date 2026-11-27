// src/api/error.ts
//
// v3 API 的错误类型与统一出口。
//
// **与 v2 完全独立**：v2 用 `src/request.ts` 的 HttpError，有自己的错误语义，
// 两边不共用类型、不互相 import。等资源全部迁完，`request.ts` 连同 HttpError
// 一起删掉即可，不会牵连这里。

import { message } from "antd";

/** 422 带回的字段级错误，可直接喂给 antd Form 的 setFields */
export type FieldErrors = Record<string, string[]>;

/**
 * 失败的种类：
 * - `http`：服务器回了非 2xx
 * - `timeout`：超时——前端等不到响应（status 0），或网关回 408 / 504
 * - `network`：请求根本没到服务器（断网、连接被拒），status 0
 */
export type ApiErrorKind = "http" | "timeout" | "network";

/**
 * v3 接口失败时抛出的错误：非 2xx、超时、网络不通都是它（见 client.ts）。
 *
 * message 取自后端 RFC 9457 的 detail，已由后端本地化；超时与网络不通没有响应体，
 * 由 client.ts 按界面语言填。
 */
export class ApiError extends Error {
  /** HTTP 状态码；超时与网络不通时为 0 */
  readonly status: number;

  readonly kind: ApiErrorKind;

  readonly fields?: FieldErrors;

  /** 已经提示过，全局兜底据此消掉重复弹窗与控制台噪音 */
  reported = false;

  constructor(
    status: number,
    message?: string,
    fields?: FieldErrors,
    kind: ApiErrorKind = "http",
  ) {
    super(message ?? `HTTP ${status}`);
    this.name = "ApiError";
    this.status = status;
    this.fields = fields;
    this.kind = kind;
  }
}

export const fieldErrorsOf = (e: unknown): FieldErrors | undefined =>
  e instanceof ApiError ? e.fields : undefined;

/**
 * 提示一条 API 错误。
 *
 * 将来要换成 Notification、或按状态码分级展示，只改这一个函数。
 */
export const reportApiError = (e: ApiError): void => {
  e.reported = true;
  // 同一文案用同一个 key：开发模式 StrictMode、ProTable 重复请求时只留一条，不叠成一串
  message.error({ content: e.message, key: toastKey(e) });
};

const toastKey = (e: Error) => `api-error:${e.message}`;

/**
 * 收掉这条错误的提示。页面已经整页显示了这个错误（ApiErrorResult）时调用，免得提示与错误页重复。
 */
export const dismissApiError = (e: unknown): void => {
  if (e instanceof ApiError) {
    message.destroy(toastKey(e));
  }
};

/**
 * 全局兜底：业务代码不 catch 时，浏览器会报 "Uncaught (in promise)"。
 * 这里把**已经提示过的** v3 错误标记为已处理，消掉那条噪音。
 *
 * 只认 ApiError，v2 的 HttpError 不受影响。
 */
export const installApiErrorHandler = (): void => {
  window.addEventListener("unhandledrejection", (event) => {
    const reason: unknown = event.reason;
    if (reason instanceof ApiError && reason.reported) {
      event.preventDefault();
    }
  });
};
