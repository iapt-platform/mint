/**
 * NetworkStatus 组件
 *
 * 依赖：
 *   - React 18
 *   - antd v6
 *   - @reduxjs/toolkit + react-redux
 *
 * 使用方式：
 *   <NetworkStatus />
 *
 * 将 netStatusSlice reducer 注册到 store：
 *   import netStatusReducer from "./netStatusSlice";
 *   // store.ts
 *   reducer: { netStatus: netStatusReducer }
 */

import React, { useCallback, useEffect, useRef, useState } from "react";
import { useDispatch, useSelector } from "react-redux";
import { Badge, Popover, Spin, Tooltip, Typography } from "antd";
import {
  ApiOutlined,
  CheckCircleFilled,
  ClockCircleOutlined,
  CloseCircleFilled,
  DisconnectOutlined,
  ExclamationCircleFilled,
  LoadingOutlined,
  MinusCircleOutlined,
  SyncOutlined,
  WifiOutlined,
} from "@ant-design/icons";

import { apiServerHealth } from "../../api/api-health";
import {
  selectNetStatus,
  statusChange,
  type ENetStatus,
  type INetStatus,
} from "../../reducers/net-status";

const { Text } = Typography;

// ─── 常量 ───────────────────────────────────────────────────────────────────

/** API 轮询间隔（毫秒），默认 60 秒 */
const POLL_INTERVAL_MS = 60_000;

/** API 请求超时阈值（毫秒） */
const API_TIMEOUT_MS = 8_000;

// ─── 辅助函数 ─────────────────────────────────────────────────────────────────

function formatTime(iso?: string): string {
  if (!iso) return "—";
  return new Date(iso).toLocaleTimeString("zh-CN", {
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
  });
}

interface StatusMeta {
  color: string;
  dotStatus: "success" | "processing" | "error" | "warning" | "default";
  icon: React.ReactNode;
  label: string;
}

function resolveStatusMeta(status: ENetStatus): StatusMeta {
  switch (status) {
    case "idle":
      return {
        color: "#8c8c8c",
        dotStatus: "default",
        icon: <MinusCircleOutlined style={{ color: "#8c8c8c" }} />,
        label: "未检测",
      };
    case "checking":
      return {
        color: "#1677ff",
        dotStatus: "processing",
        icon: <LoadingOutlined style={{ color: "#1677ff" }} spin />,
        label: "检测中…",
      };
    case "online":
      return {
        color: "#52c41a",
        dotStatus: "success",
        icon: <CheckCircleFilled style={{ color: "#52c41a" }} />,
        label: "连接正常",
      };
    case "offline":
      return {
        color: "#ff4d4f",
        dotStatus: "error",
        icon: <DisconnectOutlined style={{ color: "#ff4d4f" }} />,
        label: "网络断开",
      };
    case "api_error":
      return {
        color: "#fa8c16",
        dotStatus: "warning",
        icon: <ExclamationCircleFilled style={{ color: "#fa8c16" }} />,
        label: "API 异常",
      };
    case "api_timeout":
      return {
        color: "#fa8c16",
        dotStatus: "warning",
        icon: <CloseCircleFilled style={{ color: "#fa8c16" }} />,
        label: "API 超时",
      };
  }
}

// ─── 子组件：Popover 内容 ──────────────────────────────────────────────────────

interface PopoverContentProps {
  netStatus: INetStatus;
  onRefresh: () => void;
  refreshing: boolean;
}

const PopoverContent: React.FC<PopoverContentProps> = ({
  netStatus,
  onRefresh,
  refreshing,
}) => {
  const { status, isNetworkOnline, isApiOnline, message, lastCheckedAt } =
    netStatus;
  const meta = resolveStatusMeta(status);

  const rowStyle: React.CSSProperties = {
    display: "flex",
    alignItems: "center",
    justifyContent: "space-between",
    gap: 24,
    padding: "6px 0",
    borderBottom: "1px solid #f0f0f0",
  };

  const labelStyle: React.CSSProperties = {
    display: "flex",
    alignItems: "center",
    gap: 6,
    color: "#595959",
    fontSize: 13,
  };

  const valueStyle: React.CSSProperties = {
    display: "flex",
    alignItems: "center",
    gap: 4,
    fontSize: 13,
    fontWeight: 500,
  };

  // 网络行
  const networkOnline =
    typeof isNetworkOnline === "boolean" ? isNetworkOnline : null;
  const apiOnline = typeof isApiOnline === "boolean" ? isApiOnline : null;

  return (
    <div style={{ width: 260 }}>
      {/* 总状态 */}
      <div
        style={{
          display: "flex",
          alignItems: "center",
          gap: 8,
          marginBottom: 12,
        }}
      >
        {meta.icon}
        <Text strong style={{ fontSize: 14, color: meta.color }}>
          {meta.label}
        </Text>
        <Tooltip title="立即检测">
          <SyncOutlined
            spin={refreshing}
            onClick={onRefresh}
            style={{
              marginLeft: "auto",
              color: "#1677ff",
              cursor: "pointer",
              fontSize: 14,
            }}
          />
        </Tooltip>
      </div>

      {/* 网络状态行 */}
      <div style={rowStyle}>
        <span style={labelStyle}>
          <WifiOutlined />
          网络连接
        </span>
        <span style={valueStyle}>
          {status === "checking" ? (
            <Spin size="small" />
          ) : networkOnline === null ? (
            <Text type="secondary">—</Text>
          ) : networkOnline ? (
            <>
              <CheckCircleFilled style={{ color: "#52c41a" }} />
              <Text style={{ color: "#52c41a" }}>正常</Text>
            </>
          ) : (
            <>
              <CloseCircleFilled style={{ color: "#ff4d4f" }} />
              <Text style={{ color: "#ff4d4f" }}>断开</Text>
            </>
          )}
        </span>
      </div>

      {/* API 状态行 */}
      <div style={{ ...rowStyle, borderBottom: "none" }}>
        <span style={labelStyle}>
          <ApiOutlined />
          API 服务
        </span>
        <span style={valueStyle}>
          {status === "checking" ? (
            <Spin size="small" />
          ) : apiOnline === null ? (
            <Text type="secondary">—</Text>
          ) : apiOnline ? (
            <>
              <CheckCircleFilled style={{ color: "#52c41a" }} />
              <Text style={{ color: "#52c41a" }}>正常</Text>
            </>
          ) : (
            <>
              <CloseCircleFilled style={{ color: "#ff4d4f" }} />
              <Text style={{ color: "#ff4d4f" }}>
                {status === "api_timeout" ? "超时" : "异常"}
              </Text>
            </>
          )}
        </span>
      </div>

      {/* 错误消息 */}
      {message && (
        <div
          style={{
            marginTop: 8,
            padding: "4px 8px",
            background: "#fff7e6",
            borderRadius: 4,
            fontSize: 12,
            color: "#d46b08",
          }}
        >
          {message}
        </div>
      )}

      {/* 上次检测时间 */}
      <div
        style={{
          marginTop: 10,
          display: "flex",
          alignItems: "center",
          gap: 4,
          color: "#bfbfbf",
          fontSize: 12,
        }}
      >
        <ClockCircleOutlined />
        上次检测：{formatTime(lastCheckedAt)}
      </div>
    </div>
  );
};

// ─── 主组件 ───────────────────────────────────────────────────────────────────

export const NetworkStatus: React.FC = () => {
  const dispatch = useDispatch();
  const netStatus = useSelector(selectNetStatus);
  const [open, setOpen] = useState(false);
  const [refreshing, setRefreshing] = useState(false);
  const timerRef = useRef<ReturnType<typeof setInterval> | null>(null);

  // ── 核心检测逻辑 ──────────────────────────────────────────────────────────

  const runCheck = useCallback(async () => {
    // 标记：检测中
    dispatch(
      statusChange({
        status: "checking",
        isNetworkOnline: navigator.onLine,
        isApiOnline: undefined,
        lastCheckedAt: netStatus.lastCheckedAt,
      })
    );

    // Step 1：浏览器网络检测
    const isNetworkOnline = navigator.onLine;

    if (!isNetworkOnline) {
      dispatch(
        statusChange({
          status: "offline",
          isNetworkOnline: false,
          isApiOnline: false,
          message: "设备网络已断开，请检查网络连接",
          lastCheckedAt: new Date().toISOString(),
        })
      );
      return;
    }

    // Step 2：API 健康检测（带超时）
    let isApiOnline = false;
    let message: string | undefined;
    let finalStatus: ENetStatus = "api_error";

    try {
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), API_TIMEOUT_MS);

      await apiServerHealth();
      clearTimeout(timeoutId);

      isApiOnline = true;
      finalStatus = "online";
    } catch (err: unknown) {
      if (err instanceof Error && err.name === "AbortError") {
        finalStatus = "api_timeout";
        message = `API 请求超时（>${API_TIMEOUT_MS / 1000}s）`;
      } else {
        finalStatus = "api_error";
        message =
          err instanceof Error ? err.message : "API 服务异常，请联系管理员";
      }
    }

    dispatch(
      statusChange({
        status: finalStatus,
        isNetworkOnline: true,
        isApiOnline,
        message,
        lastCheckedAt: new Date().toISOString(),
      })
    );
  }, [dispatch, netStatus.lastCheckedAt]);

  // ── 手动刷新 ──────────────────────────────────────────────────────────────

  const handleRefresh = useCallback(async () => {
    if (refreshing) return;
    setRefreshing(true);
    await runCheck();
    setRefreshing(false);
  }, [refreshing, runCheck]);

  // ── 轮询 & 网络事件监听 ───────────────────────────────────────────────────

  useEffect(() => {
    // 首次立即检测
    runCheck();

    // 定时轮询
    timerRef.current = setInterval(runCheck, POLL_INTERVAL_MS);

    // 监听浏览器网络事件（离线时立即触发）
    const handleOffline = () => {
      dispatch(
        statusChange({
          status: "offline",
          isNetworkOnline: false,
          isApiOnline: false,
          message: "网络已断开",
          lastCheckedAt: new Date().toISOString(),
        })
      );
    };
    const handleOnline = () => runCheck();

    window.addEventListener("offline", handleOffline);
    window.addEventListener("online", handleOnline);

    return () => {
      if (timerRef.current) clearInterval(timerRef.current);
      window.removeEventListener("offline", handleOffline);
      window.removeEventListener("online", handleOnline);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // ── 渲染 ──────────────────────────────────────────────────────────────────

  const meta = resolveStatusMeta(netStatus.status);

  return (
    <Popover
      open={open}
      onOpenChange={setOpen}
      trigger="hover"
      placement="bottomRight"
      arrow={false}
      content={
        <PopoverContent
          netStatus={netStatus}
          onRefresh={handleRefresh}
          refreshing={refreshing}
        />
      }
    >
      <Badge dot status={meta.dotStatus} offset={[-2, 2]}>
        <WifiOutlined
          style={{
            fontSize: 18,
            color: meta.color,
            cursor: "pointer",
            transition: "color 0.2s",
          }}
        />
      </Badge>
    </Popover>
  );
};

export default NetworkStatus;
