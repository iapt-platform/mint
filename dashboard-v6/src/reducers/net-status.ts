import { createSlice, type PayloadAction } from "@reduxjs/toolkit";
import type { RootState } from "../store";

export type EApiStatus =
  | "pending" // 初始未检测
  | "success" // 初始未检测
  | "fail" // 检测中
  | "loading"; // 网络 + API 均正常
export interface IApiStatus {
  /** 当前状态 */
  status: EApiStatus;

  /** 可选描述信息（错误原因、HTTP 状态码等） */
  message?: string;
}
/**
 * 网络状态枚举
 *
 * idle        - 初始状态，尚未进行任何检测
 * checking    - 检测中（网络 & API 均在探测）
 * online      - 网络已连接，API 正常
 * offline     - 网络断开，无法访问互联网
 * api_error   - 网络正常，但 API 返回非 2xx 响应
 * api_timeout - 网络正常，但 API 请求超时未响应
 *
 */
export type ENetStatus =
  | "idle" // 初始未检测
  | "checking" // 检测中
  | "online" // 网络 + API 均正常
  | "offline" // 网络断开
  | "api_error" // 网络正常，API 异常
  | "api_timeout"; // 网络正常，API 超时

export interface INetStatus {
  /** 当前状态 */
  status: ENetStatus;

  /** 可选描述信息（错误原因、HTTP 状态码等） */
  message?: string;
  /** 上次成功检测时间（ISO string） */
  lastCheckedAt?: string;
  /** 网络是否在线（navigator.onLine 或 ping 结果） */
  isNetworkOnline?: boolean;
  /** API 是否可达 */
  isApiOnline?: boolean;
}

interface IState {
  status: INetStatus;
  api_status?: IApiStatus;
}

const initialState: IState = {
  status: {
    status: "idle",
  },
};

export const slice = createSlice({
  name: "netStatus",
  initialState,
  reducers: {
    statusChange: (state, action: PayloadAction<INetStatus>) => {
      state.status = action.payload;
    },
  },
});

export const { statusChange } = slice.actions;

export const selectNetStatus = (state: RootState): INetStatus =>
  state.netStatus.status;

export default slice.reducer;
