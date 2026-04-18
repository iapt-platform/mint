import { useState, useCallback } from "react";

/**
 * useMergedState - 统一管理“受控(Controlled)”与“非受控(Uncontrolled)”状态的 Hook。
 * * [用途]
 * 当一个组件需要同时支持：
 * 1. 内部自管状态（不传 value 时，组件可以自己开关/变化）。
 * 2. 外部完全掌控状态（传入 value 时，状态由父组件决定，内部调用 set 仅触发 onChange）。
 * 这种模式常用于 Modal, Input, Select 等通用 UI 组件。
 * * [参数]
 * @param defaultStateValue - 默认初始值（非受控模式下使用）。
 * @param option - 配置项，包含：
 * - value: 外部传入的受控值。
 * - onChange: 状态变更时的回调函数。
 * * [返回值]
 * @returns [mergedValue, setMergedValue]
 * - mergedValue: 最终确定的状态值。
 * - setMergedValue: 更新状态的函数（内部会自动判断是更新本地状态还是仅触发回调）。
 * * [使用示例]
 * const [open, setOpen] = useMergedState<boolean>(false, {
 * value: props.open,
 * onChange: props.onOpenChange
 * });
 */
function useMergedState<T>(
  defaultStateValue: T | (() => T),
  option?: {
    value?: T;
    onChange?: (value: T) => void;
  }
): [T, (value: T) => void] {
  const { value, onChange } = option || {};

  // 1. 内部状态：用于非受控模式
  const [innerValue, setInnerValue] = useState<T>(() => {
    if (value !== undefined) {
      return value;
    }
    return typeof defaultStateValue === "function"
      ? (defaultStateValue as () => T)()
      : defaultStateValue;
  });

  // 2. 决定最终输出的值：如果 value 存在，则为受控模式
  const mergedValue = value !== undefined ? value : innerValue;

  // 3. 定义更新函数
  const triggerChange = useCallback(
    (newValue: T) => {
      // 如果值没变，不触发更新
      if (newValue === mergedValue) return;

      // 如果是非受控模式，更新内部状态
      if (value === undefined) {
        setInnerValue(newValue);
      }

      // 无论受控还是非受控，都触发回调通知父组件
      if (onChange) {
        onChange(newValue);
      }
    },
    [value, mergedValue, onChange]
  );

  return [mergedValue, triggerChange];
}

export default useMergedState;
