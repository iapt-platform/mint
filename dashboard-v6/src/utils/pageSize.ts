/**
 * 列表「每页条数」的本地记忆（localStorage，键 `pagesize/<列表名>`）。
 *
 * 用户改过一次，下次打开同一个列表沿用；URL 里显式带了 pagesize 时以 URL 为准。
 * 读写失败（隐私模式、存储被禁用）时静默退回默认值，不影响列表本身。
 */
const keyOf = (list: string) => `pagesize/${list}`;

export function loadPageSize(list: string, fallback = 20): number {
  try {
    const value = Number(localStorage.getItem(keyOf(list)));
    return Number.isInteger(value) && value > 0 && value <= 200
      ? value
      : fallback;
  } catch {
    return fallback;
  }
}

export function savePageSize(list: string, size: number): void {
  try {
    localStorage.setItem(keyOf(list), String(size));
  } catch {
    // 存不了就算了：只是少了记忆
  }
}
