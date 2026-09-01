// hooks/useDebouncedCallback.ts
// 通用防抖回调 Hook。
// 默认尾沿触发（trailing）：连续调用时只有最后一次调用会真正执行；
// 提供 leading 立即触发、cancel 取消挂起调用、flush 立即执行挂起调用。
// debounced 返回引用稳定的函数，内部通过 ref 读取最新 callback，避免过期闭包。

import { useCallback, useEffect, useRef } from "react";

export interface IDebounceOptions {
  /** 连续调用的第一次是否立即触发，默认 false */
  leading?: boolean;
  /** 静默期结束后是否触发最后一次，默认 true */
  trailing?: boolean;
}

export interface IDebouncedCallback<Args extends unknown[]> {
  /** 防抖后的函数（引用稳定） */
  debounced: (...args: Args) => void;
  /** 取消挂起的调用 */
  cancel: () => void;
  /** 立即执行当前挂起的调用 */
  flush: () => void;
}

export function useDebouncedCallback<Args extends unknown[]>(
  callback: (...args: Args) => void,
  delay: number,
  options: IDebounceOptions = {}
): IDebouncedCallback<Args> {
  const { leading = false, trailing = true } = options;

  // 用 ref 保存最新值，debounced 内部通过 ref 读取，避免持有过期闭包
  const callbackRef = useRef(callback);
  const delayRef = useRef(delay);
  const leadingRef = useRef(leading);
  const trailingRef = useRef(trailing);

  useEffect(() => {
    callbackRef.current = callback;
    delayRef.current = delay;
    leadingRef.current = leading;
    trailingRef.current = trailing;
  });

  const timerRef = useRef<number | null>(null);
  const lastArgsRef = useRef<Args | null>(null);
  // 上一次真正执行的时刻，用于 leading 的节流判断。
  // 初始为 0：首调用时 time - 0 远大于 delay，视为可 leading。
  const lastInvokeTimeRef = useRef(0);

  const invoke = useCallback((time: number) => {
    if (lastArgsRef.current === null) return;
    const args = lastArgsRef.current;
    lastArgsRef.current = null;
    lastInvokeTimeRef.current = time;
    callbackRef.current(...args);
  }, []);

  const cancel = useCallback(() => {
    if (timerRef.current !== null) {
      window.clearTimeout(timerRef.current);
      timerRef.current = null;
    }
    lastArgsRef.current = null;
  }, []);

  const flush = useCallback(() => {
    if (timerRef.current === null) return;
    window.clearTimeout(timerRef.current);
    timerRef.current = null;
    invoke(Date.now());
  }, [invoke]);

  const debounced = useCallback(
    (...args: Args) => {
      const time = Date.now();
      lastArgsRef.current = args;

      // 距上次执行已超过 delay，且开启 leading 时，立即执行一次
      const canLeading =
        leadingRef.current &&
        time - lastInvokeTimeRef.current >= delayRef.current;

      // 清除旧的挂起定时器，重新计时（尾沿触发）
      if (timerRef.current !== null) {
        window.clearTimeout(timerRef.current);
        timerRef.current = null;
      }

      if (canLeading) {
        invoke(time);
        // 仅 leading：不排尾沿定时器，直接返回
        if (!trailingRef.current) return;
      }

      timerRef.current = window.setTimeout(() => {
        timerRef.current = null;
        if (trailingRef.current && lastArgsRef.current !== null) {
          invoke(Date.now());
        }
      }, delayRef.current);
    },
    [invoke]
  );

  // 卸载时清理定时器，避免对已卸载组件 setState
  useEffect(() => {
    return () => {
      if (timerRef.current !== null) {
        window.clearTimeout(timerRef.current);
      }
    };
  }, []);

  return { debounced, cancel, flush };
}
