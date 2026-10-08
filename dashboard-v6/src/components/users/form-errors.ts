import type { ProFormInstance } from "@ant-design/pro-components";

import { fieldErrorsOf } from "../../api/error";

/**
 * 把 v3 422 的字段级错误挂到表单输入框上。提示已由 unwrap() 弹过，这里只管定位。
 *
 * @param rename 后端字段名与表单字段名不同时的映射
 */
export const applyFieldErrors = (
  form: ProFormInstance | undefined,
  e: unknown,
  rename: Record<string, string> = {},
): void => {
  const fields = fieldErrorsOf(e);
  if (!form || !fields) {
    return;
  }
  form.setFields(
    Object.entries(fields).map(([name, errors]) => ({
      name: rename[name] ?? name,
      errors,
    })),
  );
};
