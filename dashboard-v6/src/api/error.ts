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
 * v3 接口返回非 2xx 时抛出的错误。
 *
 * message 取自后端 RFC 9457 的 detail，已由后端本地化，前端不用再编文案。
 */
export class ApiError extends Error {
  readonly status: number;

  readonly fields?: FieldErrors;

  /** 已经提示过，全局兜底据此消掉重复弹窗与控制台噪音 */
  reported = false;

  constructor(status: number, message?: string, fields?: FieldErrors) {
    super(message ?? `HTTP ${status}`);
    this.name = "ApiError";
    this.status = status;
    this.fields = fields;
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
  message.error(e.message);
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
