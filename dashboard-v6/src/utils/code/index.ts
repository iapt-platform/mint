/**
 * ============================================================
 * Script Conversion Engine — 统一文字系统转换入口
 * ============================================================
 *
 * 本模块是所有文字系统转换器的统一入口。
 * 提供：
 *
 *   script.<lang>.fromRoman(text)
 *   script.<lang>.toRoman(text)
 *
 * ------------------------------------------------------------
 * 📌 使用方式
 * ------------------------------------------------------------
 *
 * import { script } from "@/utils/code";
 *
 * script.thai.fromRoman("namo tassa");
 * script.my.toRoman("ဓမ္မ");
 *
 *
 * ------------------------------------------------------------
 * 📌 动态语言调用
 * ------------------------------------------------------------
 *
 * import { script, ScriptName } from "@/utils/code";
 *
 * function convert(
 *   lang: ScriptName,
 *   dir: "fromRoman" | "toRoman",
 *   text: string
 * ){
 *   return script[lang][dir](text);
 * }
 *
 *
 * ------------------------------------------------------------
 * 📌 添加新语言方法（标准流程）
 * ------------------------------------------------------------
 *
 * ① 在 scripts 目录创建文件
 *
 *    utils/code/scripts/lao.ts
 *
 * ② 写入结构
 *
 *    import { buildConverter } from "../core/buildConverter";
 *
 *    const romanToLao = { k:"ກ" } as const;
 *    const laoToRoman = { ກ:"k" } as const;
 *
 *    export const lao = {
 *      fromRoman: buildConverter(romanToLao),
 *      toRoman: buildConverter(laoToRoman)
 *    };
 *
 * ③ 在 index.ts 注册
 *
 *    import { lao } from "./scripts/lao";
 *
 *    export const script = {
 *      ...,
 *      lao
 *    } as const;
 *
 *
 * ------------------------------------------------------------
 * 📌 维护规范（必须遵守）
 * ------------------------------------------------------------
 *
 * ✔ 所有语言模块必须：
 *   - 使用 buildConverter()
 *   - 导出对象名必须与文件名一致
 *   - 必须包含：
 *        fromRoman
 *        toRoman
 *
 * ✔ mapping 表必须：
 *   - 使用 as const
 *   - key 不可重复
 *   - 长规则写在短规则前（或交给 buildConverter 排序）
 *
 *
 * ------------------------------------------------------------
 * 📌 不要做的事（重要）
 * ------------------------------------------------------------
 *
 * ❌ 不要直接导出函数
 * ❌ 不要在组件中写转换逻辑
 * ❌ 不要动态构造 mapping
 *
 * 所有转换规则必须写在 scripts 文件内。
 *
 *
 * ------------------------------------------------------------
 * 📌 类型系统说明
 * ------------------------------------------------------------
 *
 * ScriptName 类型自动生成：
 *
 * type ScriptName = "thai" | "my" | "si" | "taitham"
 *
 * 新语言注册后类型会自动更新。
 *
 *
 * ------------------------------------------------------------
 * 📌 架构设计原则
 * ------------------------------------------------------------
 *
 * 本系统遵循：
 *
 *   「语言是对象，转换是方法」
 *
 * 而不是：
 *
 *   「转换是函数」
 *
 * 这样设计的好处：
 *
 * - API 可读性高
 * - IDE 自动提示完整
 * - 可扩展语言
 * - 支持插件化
 * - 易维护
 *
 *
 * ============================================================
 */
import { thai } from "./scripts/thai";
import { my } from "./scripts/my";

export const script = {
  thai,
  my,
} as const;

export type ScriptName = keyof typeof script;
