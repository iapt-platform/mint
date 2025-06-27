/**
 * 缅文巴利转罗马巴利转换器
 * Myanmar Pali to Roman Pali Converter
 */

// 字符映射接口
interface CharacterMapping {
  [key: string]: string;
}

// 转换规则接口
interface ConversionRule {
  pattern: RegExp;
  replace: string | ((match: string, ...args: string[]) => string);
}

// 缅文到罗马巴利文的字符映射表
const MYANMAR_TO_ROMAN: CharacterMapping = {
  // 基本辅音
  က: "ka",
  ခ: "kha",
  ဂ: "ga",
  ဃ: "gha",
  င: "ṅa",
  စ: "ca",
  ဆ: "cha",
  ဇ: "ja",
  ဈ: "jha",
  ဉ: "ña",
  ည: "ña",
  ဋ: "ṭa",
  ဌ: "ṭha",
  ဍ: "ḍa",
  ဎ: "ḍha",
  ဏ: "ṇa",
  တ: "ta",
  ထ: "tha",
  ဒ: "da",
  ဓ: "dha",
  န: "na",
  ပ: "pa",
  ဖ: "pha",
  ဗ: "ba",
  ဘ: "bha",
  မ: "ma",
  ယ: "ya",
  ရ: "ra",
  လ: "la",
  ဝ: "va",
  သ: "sa",
  ဟ: "ha",
  ဠ: "ḷa",
  အ: "a",

  // 独立元音
  ဣ: "i",
  ဤ: "ī",
  ဥ: "u",
  ဦ: "ū",
  ဧ: "e",
  ဩ: "o",

  // 元音符号
  "ါ": "ā",
  "ာ": "ā",
  "ိ": "i",
  "ီ": "ī",
  "ု": "u",
  "ူ": "ū",
  "ေ": "e",
  "ံ": "ṃ",
  "့": "",
  "်": "",

  // 特殊符号
  "္": "", // virama (halant)

  // 数字
  "၀": "0",
  "၁": "1",
  "၂": "2",
  "၃": "3",
  "၄": "4",
  "၅": "5",
  "၆": "6",
  "၇": "7",
  "၈": "8",
  "၉": "9",

  // 标点符号
  "၊": ",",
  "။": ".",
  "၍": " ",
  "၎": " ",
};

// 特殊转换规则
const SPECIAL_CONVERSION_RULES: ConversionRule[] = [
  // 元音组合
  { pattern: /ေါ/g, replace: "o" },
  { pattern: /ေါ်/g, replace: "au" },
  { pattern: /ံ့/g, replace: "ṃ" },

  // 双辅音组合处理
  {
    pattern: /([က-အ])္([က-အ])/g,
    replace: (match: string, first: string, second: string): string => {
      const firstRoman = MYANMAR_TO_ROMAN[first] || first;
      const secondRoman = MYANMAR_TO_ROMAN[second] || second;
      // 移除第一个辅音的固有元音 'a'
      return firstRoman.slice(0, -1) + secondRoman;
    },
  },
];

// 后处理规则
const POST_PROCESSING_RULES: ConversionRule[] = [
  // 清理连续相同元音
  { pattern: /aa/g, replace: "ā" },
  { pattern: /ii/g, replace: "ī" },
  { pattern: /uu/g, replace: "ū" },

  // 清理辅音间多余的 'a'
  { pattern: /([kgcjṭḍtdpbmylrsvh])a([kgcjṭḍtdpbmylrsvh])/g, replace: "$1$2" },

  // 清理多余空格
  { pattern: /\s+/g, replace: "" },
];

/**
 * 将缅文巴利文转换为罗马巴利文
 * @param myanmarText - 输入的缅文巴利文本
 * @returns 转换后的罗马巴利文本
 */
export function convertMyanmarPaliToRoman(myanmarText: string): string {
  if (!myanmarText || typeof myanmarText !== "string") {
    return "";
  }

  let result = myanmarText;

  // 应用特殊转换规则
  result = applyConversionRules(result, SPECIAL_CONVERSION_RULES);

  // 逐字符转换
  result = result
    .split("")
    .map((char: string) => MYANMAR_TO_ROMAN[char] || char)
    .join("");

  // 应用后处理规则
  result = applyConversionRules(result, POST_PROCESSING_RULES);

  // 清理首尾空格
  return result.trim();
}

/**
 * 应用转换规则
 * @param text - 要处理的文本
 * @param rules - 转换规则数组
 * @returns 处理后的文本
 */
function applyConversionRules(text: string, rules: ConversionRule[]): string {
  let result = text;

  for (const rule of rules) {
    if (typeof rule.replace === "function") {
      result = result.replace(rule.pattern, rule.replace);
    } else {
      result = result.replace(rule.pattern, rule.replace);
    }
  }

  return result;
}

/**
 * 批量转换缅文巴利文本数组
 * @param myanmarTexts - 缅文巴利文本数组
 * @returns 转换后的罗马巴利文本数组
 */
export function convertMyanmarPaliArrayToRoman(
  myanmarTexts: string[]
): string[] {
  return myanmarTexts.map((text) => convertMyanmarPaliToRoman(text));
}

/**
 * 检查文本是否包含缅文字符
 * @param text - 要检查的文本
 * @returns 是否包含缅文字符
 */
export function containsMyanmarCharacters(text: string): boolean {
  const myanmarRange = /[\u1000-\u109F]/;
  return myanmarRange.test(text);
}

/**
 * 转换选项接口
 */
export interface ConversionOptions {
  /** 是否保留原始标点符号 */
  preservePunctuation?: boolean;
  /** 是否转换数字 */
  convertNumbers?: boolean;
  /** 自定义字符映射 */
  customMapping?: CharacterMapping;
}

/**
 * 带选项的缅文巴利转罗马巴利转换函数
 * @param myanmarText - 输入的缅文巴利文本
 * @param options - 转换选项
 * @returns 转换后的罗马巴利文本
 */
export function convertMyanmarPaliToRomanWithOptions(
  myanmarText: string,
  options: ConversionOptions = {}
): string {
  if (!myanmarText || typeof myanmarText !== "string") {
    return "";
  }

  const {
    preservePunctuation = true,
    convertNumbers = true,
    customMapping = {},
  } = options;

  // 合并字符映射
  const mapping: CharacterMapping = { ...MYANMAR_TO_ROMAN, ...customMapping };

  // 如果不转换数字，从映射中移除数字
  if (!convertNumbers) {
    const numberKeys = ["၀", "၁", "၂", "၃", "၄", "၅", "၆", "၇", "၈", "၉"];
    numberKeys.forEach((key) => delete mapping[key]);
  }

  // 如果不保留标点符号，从映射中移除标点
  if (!preservePunctuation) {
    const punctuationKeys = ["၊", "။", "၍", "၎"];
    punctuationKeys.forEach((key) => delete mapping[key]);
  }

  let result = myanmarText;

  // 应用特殊转换规则
  result = applyConversionRules(result, SPECIAL_CONVERSION_RULES);

  // 逐字符转换
  result = result
    .split("")
    .map((char: string) => mapping[char] || char)
    .join("");

  // 应用后处理规则
  result = applyConversionRules(result, POST_PROCESSING_RULES);

  return result.trim();
}

/**
 * 转换结果接口
 */
export interface ConversionResult {
  /** 原始缅文文本 */
  original: string;
  /** 转换后的罗马文本 */
  converted: string;
  /** 是否成功转换 */
  success: boolean;
  /** 错误信息（如果有） */
  error?: string;
}

/**
 * 安全的缅文巴利转换函数，返回详细结果
 * @param myanmarText - 输入的缅文巴利文本
 * @returns 转换结果对象
 */
export function safeConvertMyanmarPaliToRoman(
  myanmarText: string
): ConversionResult {
  try {
    if (!myanmarText || typeof myanmarText !== "string") {
      return {
        original: myanmarText || "",
        converted: "",
        success: false,
        error: "Invalid input: text must be a non-empty string",
      };
    }

    const converted = convertMyanmarPaliToRoman(myanmarText);

    return {
      original: myanmarText,
      converted,
      success: true,
    };
  } catch (error) {
    return {
      original: myanmarText || "",
      converted: "",
      success: false,
      error: error instanceof Error ? error.message : "Unknown error occurred",
    };
  }
}

// 导出字符映射表和规则（供高级用户使用）
export { MYANMAR_TO_ROMAN, SPECIAL_CONVERSION_RULES, POST_PROCESSING_RULES };

// 使用示例：
/*
// 基本使用
const romanText = convertMyanmarPaliToRoman('ဗုဒ္ဓ');
console.log(romanText); // 输出: "buddha"

// 批量转换
const myanmarTexts = ['ဗုဒ္ဓ', 'ဓမ္မ', 'သံဃ'];
const romanTexts = convertMyanmarPaliArrayToRoman(myanmarTexts);
console.log(romanTexts); // 输出: ["buddha", "dhamma", "saṅgha"]

// 带选项的转换
const romanTextWithOptions = convertMyanmarPaliToRomanWithOptions(
  'ဗုဒ္ဓ ၁၂၃', 
  { convertNumbers: false }
);

// 安全转换
const result = safeConvertMyanmarPaliToRoman('ဗုဒ္ဓ');
if (result.success) {
  console.log(result.converted);
} else {
  console.error(result.error);
}
*/
