/**
 * 模板生成器能生成的模板。与后端 `App\Services\Markup\Templates` 一一对应：
 * 模板名、参数名取自各模板的 `params()`，取值范围取自它的注释与前端组件。
 *
 * 不在这里的：`toggle`（`- <div>…</div>` 列表项由解析器自动生成）、内部模板 `anno-*`（程序注入）、
 * `unknown`（没登记的名字）。别名 `n`（= note）只生成正式名字。
 */

/** 表单控件 */
export type TplFieldKind =
  | "text"
  | "textarea"
  | "number"
  | "select"
  /** 文本框 + 选文章 */
  | "article"
  /** 文本框 + 选 channel */
  | "channel"
  /** 文本框 + 从网盘选文件 */
  | "attachment";

export interface TplField {
  /** 参数名（后端 ParamSpec 的键） */
  name: string;
  kind: TplFieldKind;
  /** select 的取值 */
  options?: string[];
  /** 缺省值：表单初始值，也是后端的默认值（与它相同时不写进代码） */
  defaultValue?: string;
  placeholder?: string;
  required?: boolean;
  /** 只在别的参数取某些值时显示 */
  visibleWhen?: { field: string; values: string[] };
}

export interface TplSpec {
  /** 模板名（`{{名字|…}}`）；`Tpl:` 是用户自定义模板 */
  name: string;
  fields: TplField[];
  /** 不走 `{{名字|k=v}}` 的，自己拼代码 */
  build?: (values: TplValues) => string;
}

export type TplValues = Record<string, string | undefined>;

/** 前端 DisplayWrapper 支持的显示方式（types/template.ts 的 TDisplayStyle） */
const DISPLAY_STYLES = ["modal", "card", "toggle", "link", "window", "popover"];

/** 嵌入经文的模板（chapter、para）另有行内引用 reference：不写标题时显示「缩写 + 段号」 */
const PARAGRAPH_STYLES = [...DISPLAY_STYLES, "reference"];

const style = (
  defaultValue?: string,
  options: string[] = DISPLAY_STYLES,
): TplField => ({
  name: "style",
  kind: "select",
  options,
  defaultValue,
});

export const TEMPLATES: TplSpec[] = [
  {
    name: "article",
    fields: [
      {
        name: "type",
        kind: "select",
        options: ["article", "chapter", "para", "anthology"],
        defaultValue: "article",
        required: true,
      },
      { name: "id", kind: "article", placeholder: "uuid / book-para" },
      {
        name: "book",
        kind: "number",
        visibleWhen: { field: "type", values: ["chapter"] },
      },
      {
        name: "paragraphs",
        kind: "number",
        visibleWhen: { field: "type", values: ["chapter"] },
      },
      { name: "title", kind: "text" },
      { name: "channel", kind: "channel" },
      {
        name: "anthology",
        kind: "text",
        placeholder: "uuid",
        visibleWhen: { field: "type", values: ["article"] },
      },
      style(),
    ],
  },
  {
    name: "chapter",
    fields: [
      { name: "id", kind: "text", placeholder: "book-para" },
      { name: "book", kind: "number" },
      { name: "para", kind: "number" },
      { name: "title", kind: "text" },
      { name: "channel", kind: "channel" },
      style(undefined, PARAGRAPH_STYLES),
    ],
  },
  {
    name: "para",
    fields: [
      { name: "id", kind: "text", placeholder: "book-para", required: true },
      { name: "title", kind: "text" },
      style(undefined, PARAGRAPH_STYLES),
    ],
  },
  {
    name: "sent",
    fields: [
      {
        name: "id",
        kind: "text",
        placeholder: "book-para-start-end",
        required: true,
      },
      { name: "channel", kind: "channel" },
      {
        name: "text",
        kind: "select",
        options: ["both", "origin", "translation"],
        defaultValue: "both",
      },
    ],
  },
  {
    name: "quote",
    fields: [
      { name: "para", kind: "text", placeholder: "book-para", required: true },
    ],
  },
  {
    name: "ql",
    fields: [
      {
        name: "type",
        kind: "select",
        // 印本版本（page_numbers.type，大小写不论）；c 按章节路径取书名与章名
        options: ["c", "p", "m", "v", "t", "o"],
        required: true,
      },
      { name: "book", kind: "number" },
      { name: "para", kind: "number" },
      { name: "bookname", kind: "text" },
      { name: "volume", kind: "number" },
      { name: "page", kind: "number" },
      { name: "title", kind: "text" },
      style("modal"),
    ],
  },
  {
    name: "term",
    fields: [
      {
        name: "word",
        kind: "text",
        placeholder: "word / word@tag / word#text",
        required: true,
      },
    ],
    // `[[word]]` 是 term 的简写，最常用
    build: (v) => (v.word ? `[[${v.word}]]` : "[[]]"),
  },
  {
    name: "g",
    fields: [{ name: "word", kind: "text", required: true }],
  },
  {
    name: "note",
    fields: [
      { name: "text", kind: "textarea", required: true },
      { name: "trigger", kind: "text" },
      { name: "cite", kind: "text" },
      { name: "citelink", kind: "text", placeholder: "book-para-start-end" },
    ],
    // 正文放第一个位置参数：E2 方言下正文里出现 `|`、`a=b` 都不会被拆开
    build: (v) => tplCode("note", omit(v, "text"), v.text ?? ""),
  },
  {
    name: "qa",
    fields: [
      {
        name: "id",
        kind: "text",
        placeholder: "discussion uuid",
        required: true,
      },
      style(),
    ],
  },
  {
    name: "v",
    fields: [
      { name: "url", kind: "attachment", required: true },
      { name: "title", kind: "text" },
      style("modal"),
    ],
  },
  {
    name: "nissaya",
    fields: [
      { name: "pali", kind: "text", required: true },
      { name: "meaning", kind: "text", placeholder: "meaning=meaning2" },
    ],
  },
  {
    name: "exercise",
    fields: [
      { name: "id", kind: "text", required: true },
      { name: "title", kind: "text" },
    ],
  },
  {
    name: "ai",
    fields: [
      { name: "title", kind: "text" },
      { name: "message", kind: "textarea" },
      {
        name: "style",
        kind: "select",
        options: ["block", "tag"],
        defaultValue: "block",
      },
    ],
  },
  {
    name: "category",
    fields: [{ name: "name", kind: "text", required: true }],
  },
  {
    name: "quality",
    fields: [
      {
        name: "value",
        kind: "select",
        options: ["featured", "standard", "draft", "pending"],
        defaultValue: "pending",
        required: true,
      },
    ],
    // 缺省值也要写出来：`{{quality}}` 读起来看不出是哪一档
    build: (v) => `{{quality|${v.value ?? "pending"}}}`,
  },
  {
    name: "ref",
    fields: [{ name: "title", kind: "text" }],
  },
  {
    name: "toc",
    fields: [
      { name: "levels", kind: "text", placeholder: "2-3", defaultValue: "2-3" },
      { name: "title", kind: "text" },
    ],
  },
  {
    name: "dict-pref",
    fields: [
      { name: "page", kind: "number", defaultValue: "1" },
      { name: "size", kind: "number", defaultValue: "100" },
    ],
  },
  {
    name: "mermaid",
    fields: [
      {
        name: "text",
        kind: "textarea",
        placeholder: "graph LR\n  A --> B",
        required: true,
      },
    ],
    // 图表写成 ```mermaid 围栏，解析时转成 mermaid 模板
    build: (v) => "```mermaid\n" + (v.text ?? "") + "\n```",
  },
  {
    name: "Tpl:",
    fields: [
      { name: "name", kind: "text", required: true },
      { name: "params", kind: "textarea", placeholder: "value\nname=value" },
    ],
    // 以文章为模板：参数原样交给 Mustache（{{1}}、{{b}}），每行一个
    build: (v) => {
      const params = (v.params ?? "")
        .split("\n")
        .map((line) => line.trim())
        .filter((line) => line !== "");
      return `{{${["Tpl:" + (v.name ?? ""), ...params].join("|")}}}`;
    },
  },
];

const omit = (values: TplValues, name: string): TplValues => {
  const rest = { ...values };
  delete rest[name];
  return rest;
};

/** `{{名字|位置参数|k=v|…}}`：空值与等于缺省值的参数不写 */
const tplCode = (
  name: string,
  values: TplValues,
  positional?: string,
): string => {
  const spec = TEMPLATES.find((t) => t.name === name);
  const named = (spec?.fields ?? [])
    .filter((f) => isVisible(f, values))
    .filter((f) => {
      const value = values[f.name];
      if (value === undefined || value === "") return false;
      // 必填的照写（后端没有缺省值）；可选的等于缺省值就省掉
      return f.required || value !== f.defaultValue;
    })
    .map((f) => `${f.name}=${values[f.name]}`);
  const parts = [
    name,
    ...(positional !== undefined ? [positional] : []),
    ...named,
  ];
  return `{{${parts.join("|")}}}`;
};

/** 模板名 → 文案键里的名字（`Tpl:` 去掉冒号） */
export const tplKey = (name: string) => name.replace(":", "");

export const isVisible = (field: TplField, values: TplValues): boolean =>
  !field.visibleWhen ||
  field.visibleWhen.values.includes(values[field.visibleWhen.field] ?? "");

/** 按表单的值生成模板代码 */
export const buildTplCode = (spec: TplSpec, values: TplValues): string =>
  spec.build ? spec.build(values) : tplCode(spec.name, values);

/** 表单初始值：各参数的缺省值 */
export const initialValues = (spec: TplSpec): TplValues =>
  Object.fromEntries(
    spec.fields
      .filter((f) => f.defaultValue !== undefined)
      .map((f) => [f.name, f.defaultValue]),
  );
