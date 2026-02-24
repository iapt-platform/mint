#!/usr/bin/env node

const { execSync } = require("child_process");
const fs = require("fs");
const path = require("path");

console.log("🔍 TypeScript 错误分析工具 v2\n");
console.log("=".repeat(80) + "\n");

/**
 * 运行 TypeScript 编译检查
 */
function getTscErrors() {
  console.log("📊 正在运行 TypeScript 检查...\n");

  try {
    const result = execSync("npx tsc --noEmit -p tsconfig.app.json 2>&1", {
      cwd: path.resolve(__dirname, ".."),
      encoding: "utf-8",
      env: { ...process.env, NODE_NO_WARNINGS: "1" },
    });

    if (!result || result.trim().length === 0) {
      console.log("✅ 没有类型错误！");
      return "";
    }

    return result;
  } catch (error) {
    return error.stdout || error.stderr || error.message || "";
  }
}

/**
 * 保存原始输出
 */
function saveRawOutput(output) {
  const rawPath = path.join(__dirname, "..", "tsc-raw-output.txt");
  fs.writeFileSync(rawPath, output, "utf-8");
  console.log(`📝 原始输出已保存: ${path.basename(rawPath)}\n`);
}

/**
 * 解析错误信息
 * 支持格式: src/file.tsx(1,22): error TS2307: Error message
 */
function parseErrors(errorOutput) {
  const lines = errorOutput.split("\n");
  const errorsByType = new Map();
  let parsedCount = 0;

  console.log(`📄 总行数: ${lines.length}\n`);
  console.log("🔍 开始解析错误...\n");

  for (let i = 0; i < lines.length; i++) {
    const line = lines[i].trim();

    // 匹配格式: src/file.tsx(1,22): error TS2307: Error message
    const errorMatch = line.match(
      /^(.+?)\((\d+),(\d+)\):\s*error\s+(TS\d+):\s*(.+)$/
    );

    if (errorMatch) {
      parsedCount++;
      const [, filePath, lineNum, colNum, errorCode, errorMessage] = errorMatch;

      const errorObj = {
        filePath: filePath.trim(),
        lineNum: parseInt(lineNum),
        colNum: parseInt(colNum),
        errorCode,
        errorMessage: errorMessage.trim(),
        fullError: line,
      };

      // 按错误代码分组
      if (!errorsByType.has(errorCode)) {
        errorsByType.set(errorCode, {
          code: errorCode,
          count: 0,
          samples: [],
          description: errorMessage.trim(),
        });
      }

      const errorType = errorsByType.get(errorCode);
      errorType.count++;

      // 只保存前 2 个样本
      if (errorType.samples.length < 2) {
        errorType.samples.push(errorObj);
      }

      // 每 100 个输出进度
      if (parsedCount % 100 === 0) {
        process.stdout.write(`\r   已解析 ${parsedCount} 个错误...`);
      }
    }
  }

  if (parsedCount > 0) {
    process.stdout.write(`\r   已解析 ${parsedCount} 个错误...完成\n\n`);
  }

  return errorsByType;
}

/**
 * 生成 Markdown 报告
 */
function generateMarkdownReport(errorsByType) {
  let report = "";

  report += "# TypeScript 错误分析报告\n\n";
  report += `生成时间: ${new Date().toLocaleString("zh-CN")}\n\n`;
  report += "=".repeat(80) + "\n\n";

  // 统计
  const totalErrors = Array.from(errorsByType.values()).reduce(
    (sum, e) => sum + e.count,
    0
  );
  report += "## 📊 错误统计\n\n";
  report += `- **总错误数**: ${totalErrors}\n`;
  report += `- **错误类型**: ${errorsByType.size} 种\n\n`;

  // 排序
  const sortedErrors = Array.from(errorsByType.values()).sort(
    (a, b) => b.count - a.count
  );

  report += "### 错误分布\n\n";
  report += "| 排名 | 错误代码 | 数量 | 占比 | 描述 |\n";
  report += "|------|---------|------|------|------|\n";

  sortedErrors.forEach((error, index) => {
    const percentage = ((error.count / totalErrors) * 100).toFixed(1);
    const desc =
      error.description.substring(0, 60) +
      (error.description.length > 60 ? "..." : "");
    report += `| ${index + 1} | ${error.code} | ${error.count} | ${percentage}% | ${desc} |\n`;
  });

  report += "\n" + "=".repeat(80) + "\n\n";

  // 详细样本
  report += "## 📋 错误详细样本 (每种类型 2 个)\n\n";

  sortedErrors.forEach((error, index) => {
    report += `### ${index + 1}. ${error.code} - ${error.count} 个错误\n\n`;
    report += `**完整描述**: ${error.description}\n\n`;

    if (error.samples.length > 0) {
      report += "**样本:**\n\n";
      error.samples.forEach((sample, sampleIndex) => {
        report += `#### 样本 ${sampleIndex + 1}\n\n`;
        report += "```\n";
        report += `${sample.fullError}\n`;
        report += "```\n\n";
      });
    }

    report += "---\n\n";
  });

  return report;
}

/**
 * 生成 JSON 报告
 */
function generateJsonReport(errorsByType) {
  const sortedErrors = Array.from(errorsByType.values()).sort(
    (a, b) => b.count - a.count
  );

  return JSON.stringify(
    {
      generatedAt: new Date().toISOString(),
      summary: {
        totalErrors: sortedErrors.reduce((sum, e) => sum + e.count, 0),
        errorTypes: errorsByType.size,
      },
      errors: sortedErrors.map((error) => ({
        code: error.code,
        count: error.count,
        description: error.description,
        percentage: (
          (error.count / sortedErrors.reduce((sum, e) => sum + e.count, 0)) *
          100
        ).toFixed(2),
        samples: error.samples,
      })),
    },
    null,
    2
  );
}

/**
 * 主函数
 */
function main() {
  // 获取错误
  const errorOutput = getTscErrors();

  if (!errorOutput || errorOutput.trim().length === 0) {
    console.log("✅ 没有 TypeScript 错误！\n");
    return;
  }

  // 保存原始输出
  saveRawOutput(errorOutput);

  // 解析错误
  const errorsByType = parseErrors(errorOutput);

  if (errorsByType.size === 0) {
    console.log("⚠️  无法解析错误信息\n");
    console.log("💡 请检查 tsc-raw-output.txt 查看原始输出\n");
    return;
  }

  // 生成报告
  console.log("📝 正在生成报告...\n");
  const mdReport = generateMarkdownReport(errorsByType);
  const jsonReport = generateJsonReport(errorsByType);

  // 保存文件
  const mdPath = path.join(__dirname, "..", "tsc-errors-report.md");
  const jsonPath = path.join(__dirname, "..", "tsc-errors-report.json");

  fs.writeFileSync(mdPath, mdReport, "utf-8");
  fs.writeFileSync(jsonPath, jsonReport, "utf-8");

  // 输出摘要
  const sortedErrors = Array.from(errorsByType.values()).sort(
    (a, b) => b.count - a.count
  );

  const totalErrors = sortedErrors.reduce((sum, e) => sum + e.count, 0);

  console.log("=".repeat(80));
  console.log("✅ 分析完成");
  console.log("=".repeat(80));
  console.log(`\n📊 总错误数: ${totalErrors}`);
  console.log(`📊 错误类型: ${errorsByType.size} 种\n`);

  console.log("Top 10 错误类型:\n");
  sortedErrors.slice(0, 10).forEach((error, index) => {
    const percentage = ((error.count / totalErrors) * 100).toFixed(1);
    const countStr = String(error.count).padStart(4);
    const pctStr = String(percentage).padStart(5);
    console.log(
      `  ${(index + 1).toString().padStart(2)}. ${error.code.padEnd(8)} ${countStr} 个 (${pctStr}%)`
    );
  });

  console.log("\n" + "=".repeat(80));
  console.log("📄 报告已生成:\n");
  console.log(`  - tsc-errors-report.md`);
  console.log(`  - tsc-errors-report.json`);
  console.log("=".repeat(80) + "\n");

  console.log("💡 下一步:\n");
  console.log("  cat ../tsc-errors-report.md\n");
  console.log("  把报告内容发给 AI，获取修复方案\n");
}

main();
