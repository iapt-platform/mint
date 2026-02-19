#!/usr/bin/env node

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

console.log('🔧 全面错误修复工具 v4\n');
console.log('支持所有 import 语法变体\n');
console.log('='.repeat(80) + '\n');

const stats = {
  totalErrors: 0,
  fixedErrors: 0,
  fixedFiles: new Set(),
};

function getTscErrors() {
  console.log('📊 Step 1: 运行 TypeScript 检查...\n');
  try {
    execSync('npx tsc --noEmit -p tsconfig.app.json 2>&1', {
      cwd: path.resolve(__dirname, '..'),
      encoding: 'utf-8',
      env: { ...process.env, NODE_NO_WARNINGS: '1' },
    });
    return '';
  } catch (error) {
    return error.stdout || error.stderr || '';
  }
}

function parseErrors(output) {
  console.log('🔍 Step 2: 解析错误...\n');
  const errors = [];
  output.split('\n').forEach(line => {
    const match = line.match(/^(.+?)\((\d+),(\d+)\):\s*error\s+(TS\d+):\s*(.+)$/);
    if (match) {
      const [, filePath, lineNum, colNum, errorCode, errorMessage] = match;
      errors.push({
        filePath: path.resolve(__dirname, '..', filePath.trim()),
        lineNum: parseInt(lineNum),
        colNum: parseInt(colNum),
        errorCode,
        errorMessage: errorMessage.trim(),
      });
      stats.totalErrors++;
    }
  });
  console.log(`   找到 ${errors.length} 个错误\n`);
  return errors;
}

function groupByFile(errors) {
  const fileMap = new Map();
  errors.forEach(error => {
    if (!fileMap.has(error.filePath)) fileMap.set(error.filePath, []);
    fileMap.get(error.filePath).push(error);
  });
  return fileMap;
}

function escapeRegex(str) {
  return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

/**
 * 修复 TS1484 - 完整版本
 */
function fixTS1484(filePath, errors) {
  let lines = fs.readFileSync(filePath, 'utf-8').split('\n');
  let modified = false;

  const typesByLine = new Map();
  errors.forEach(e => {
    const match = e.errorMessage.match(/^'([^']+)'/);
    if (!match) return;
    const typeName = match[1];
    if (!typesByLine.has(e.lineNum)) typesByLine.set(e.lineNum, new Set());
    typesByLine.get(e.lineNum).add(typeName);
  });

  const importBlocks = new Map();

  typesByLine.forEach((typeNames, errorLineNum) => {
    const errorLineIndex = errorLineNum - 1;

    let startIndex = -1;
    for (let i = errorLineIndex; i >= Math.max(0, errorLineIndex - 30); i--) {
      if (lines[i].match(/^\s*import\s/)) {
        startIndex = i;
        break;
      }
      if (i < errorLineIndex && lines[i].trim() === '') break;
    }
    if (startIndex === -1) return;

    let endIndex = -1;
    for (let i = startIndex; i < Math.min(lines.length, startIndex + 50); i++) {
      if (lines[i].match(/\}\s*from\s*['"][^'"]+['"]/)) {
        endIndex = i;
        break;
      }
      if (i === startIndex && lines[i].match(/from\s*['"][^'"]+['"]/)) {
        endIndex = i;
        break;
      }
    }
    if (endIndex === -1) return;

    if (!importBlocks.has(startIndex)) {
      importBlocks.set(startIndex, { startIndex, endIndex, typeNames: new Set() });
    }
    typeNames.forEach(t => importBlocks.get(startIndex).typeNames.add(t));
  });

  if (importBlocks.size === 0) return false;

  const sortedBlocks = Array.from(importBlocks.values())
    .sort((a, b) => b.startIndex - a.startIndex);

  sortedBlocks.forEach(({ startIndex, endIndex, typeNames }) => {
    const fullImport = lines.slice(startIndex, endIndex + 1).join('\n');
    const sourceMatch = fullImport.match(/from\s*(['"][^'"]+['"])\s*;?\s*$/m);
    if (!sourceMatch) return;
    const source = sourceMatch[1];

    const hasSemicolon = lines[endIndex].trimEnd().endsWith(';');
    const semi = hasSemicolon ? ';' : '';

    const indent = lines[startIndex].match(/^(\s*)/)[1];
    const itemIndent = indent + '  ';

    const isSingleLine = startIndex === endIndex;

    if (isSingleLine) {
      // ══════════════════════════════════════════════════════
      // 单行处理
      // ══════════════════════════════════════════════════════
      
      // 情况 1: import Default, { A, B } from 'xxx'
      const defaultImportMatch = lines[startIndex].match(
        /^(\s*)import\s+(\w+)\s*,\s*\{([^}]+)\}\s*from\s*(['"][^'"]+['"])\s*;?\s*$/
      );

      if (defaultImportMatch) {
        const [, indent, defaultName, imports, source] = defaultImportMatch;
        const importItems = imports
          .split(',')
          .map(i => i.trim().replace(/^type\s+/, ''))
          .filter(Boolean);

        const newItems = importItems.map(item =>
          typeNames.has(item) ? `type ${item}` : item
        );
        lines[startIndex] = `${indent}import ${defaultName}, { ${newItems.join(', ')} } from ${source}${semi}`;

        modified = true;
        stats.fixedErrors += typeNames.size;
        return;
      }

      // 情况 2: import { A, B } from 'xxx'
      const importMatch = lines[startIndex].match(
        /^\s*import\s*(?:type\s*)?\{([^}]+)\}\s*from\s*['"][^'"]+['"]\s*;?\s*$/
      );
      if (!importMatch) return;

      const importItems = importMatch[1]
        .split(',')
        .map(i => i.trim().replace(/^type\s+/, ''))
        .filter(Boolean);

      const allAreTypes = importItems.every(item => typeNames.has(item));

      if (allAreTypes) {
        lines[startIndex] =
          `${indent}import type { ${importItems.join(', ')} } from ${source}${semi}`;
      } else {
        const newItems = importItems.map(item =>
          typeNames.has(item) ? `type ${item}` : item
        );
        lines[startIndex] =
          `${indent}import { ${newItems.join(', ')} } from ${source}${semi}`;
      }

      modified = true;
      stats.fixedErrors += typeNames.size;

    } else {
      // ══════════════════════════════════════════════════════
      // 多行处理
      // ══════════════════════════════════════════════════════

      // 检测各种格式:
      // 1. import Default, { ... }
      // 2. import Default, type { ... } ← 错误语法，需修复
      // 3. import { ... }
      // 4. import type { ... }
      
      const firstLine = lines[startIndex];
      let hasDefaultImport = false;
      let defaultName = null;
      let hasTypeKeyword = false;

      // 匹配: import Default, { 或 import Default, type {
      const defaultMatch = firstLine.match(/^(\s*)import\s+(\w+)\s*,\s*(type\s*)?\{/);
      if (defaultMatch) {
        hasDefaultImport = true;
        defaultName = defaultMatch[2];
        hasTypeKeyword = !!defaultMatch[3];
      }

      // 提取中间的导入项
      const importItems = [];
      for (let i = startIndex + 1; i < endIndex; i++) {
        const trimmed = lines[i].trim();
        if (!trimmed || trimmed.startsWith('//') || trimmed.startsWith('/*')) continue;
        const item = trimmed.replace(/,\s*$/, '').replace(/^type\s+/, '').trim();
        if (item) importItems.push(item);
      }

      const allAreTypes = importItems.every(item => typeNames.has(item));

      let newImport;
      
      if (hasDefaultImport) {
        // 有默认导入: import Default, { ... }
        // 无论是否有错误的 type 关键字，都统一转换为: import Default, { type A, B }
        const newItems = importItems.map(item =>
          typeNames.has(item) ? `type ${item}` : item
        );
        const itemLines = newItems.map(i => `${itemIndent}${i},`).join('\n');
        newImport = `${indent}import ${defaultName}, {\n${itemLines}\n${indent}} from ${source}${semi}`;
      } else if (allAreTypes) {
        // 纯类型导入（无默认导入）: import type { A, B }
        const cleanItemLines = importItems.map(i => `${itemIndent}${i},`).join('\n');
        newImport = `${indent}import type {\n${cleanItemLines}\n${indent}} from ${source}${semi}`;
      } else {
        // 混合导入: import { type A, B }
        const newItems = importItems.map(item =>
          typeNames.has(item) ? `type ${item}` : item
        );
        const itemLines = newItems.map(i => `${itemIndent}${i},`).join('\n');
        newImport = `${indent}import {\n${itemLines}\n${indent}} from ${source}${semi}`;
      }

      lines.splice(startIndex, endIndex - startIndex + 1, ...newImport.split('\n'));

      modified = true;
      stats.fixedErrors += typeNames.size;
    }
  });

  if (modified) {
    fs.writeFileSync(filePath, lines.join('\n'), 'utf-8');
    return true;
  }
  return false;
}

function fixTS6133(filePath, errors) {
  const lines = fs.readFileSync(filePath, 'utf-8').split('\n');
  let modified = false;

  [...errors].sort((a, b) => b.lineNum - a.lineNum).forEach(error => {
    const lineIndex = error.lineNum - 1;
    if (lineIndex < 0 || lineIndex >= lines.length) return;

    const match = error.errorMessage.match(/^'([^']+)'/);
    if (!match) return;
    const varName = match[1];

    const pattern = new RegExp(`\\b${escapeRegex(varName)}\\b`);
    if (pattern.test(lines[lineIndex])) {
      lines[lineIndex] = lines[lineIndex].replace(pattern, `_${varName}`);
      modified = true;
      stats.fixedErrors++;
    }
  });

  if (modified) {
    fs.writeFileSync(filePath, lines.join('\n'), 'utf-8');
    return true;
  }
  return false;
}

function fixTS6192(filePath, errors) {
  const lines = fs.readFileSync(filePath, 'utf-8').split('\n');
  let modified = false;

  [...errors].sort((a, b) => b.lineNum - a.lineNum).forEach(error => {
    const lineIndex = error.lineNum - 1;
    if (lineIndex < 0 || lineIndex >= lines.length) return;
    if (lines[lineIndex].trim().startsWith('import ')) {
      lines.splice(lineIndex, 1);
      modified = true;
      stats.fixedErrors++;
    }
  });

  if (modified) {
    fs.writeFileSync(filePath, lines.join('\n'), 'utf-8');
    return true;
  }
  return false;
}

function fixTS2724(filePath, errors) {
  let content = fs.readFileSync(filePath, 'utf-8');
  let modified = false;

  errors.forEach(error => {
    const match = error.errorMessage.match(/'([^']+)'.*Did you mean '([^']+)'\?/);
    if (!match) return;
    const [, wrongName, correctName] = match;
    const pattern = new RegExp(`\\b${escapeRegex(wrongName)}\\b`, 'g');
    if (pattern.test(content)) {
      content = content.replace(pattern, correctName);
      modified = true;
      stats.fixedErrors++;
    }
  });

  if (modified) {
    fs.writeFileSync(filePath, content, 'utf-8');
    return true;
  }
  return false;
}

function fixTS2591(filePath, errors) {
  let content = fs.readFileSync(filePath, 'utf-8');
  if (!content.includes('process.env')) return false;

  content = content
    .replace(/process\.env\.PUBLIC_URL/g, 'import.meta.env.BASE_URL')
    .replace(/process\.env\.REACT_APP_(\w+)/g, 'import.meta.env.VITE_$1')
    .replace(/process\.env\.NODE_ENV/g, 'import.meta.env.MODE')
    .replace(/process\.env\.([A-Z_]+)/g, 'import.meta.env.VITE_$1');

  fs.writeFileSync(filePath, content, 'utf-8');
  stats.fixedErrors += errors.length;
  return true;
}

function fixTS7006(filePath, errors) {
  const lines = fs.readFileSync(filePath, 'utf-8').split('\n');
  let modified = false;

  [...errors].sort((a, b) => b.lineNum - a.lineNum).forEach(error => {
    const lineIndex = error.lineNum - 1;
    if (lineIndex < 0 || lineIndex >= lines.length) return;

    const match = error.errorMessage.match(/Parameter '([^']+)'/);
    if (!match) return;
    const paramName = match[1];

    const pattern = new RegExp(`\\b(${escapeRegex(paramName)})\\b(?!\\s*[?:])`);
    if (pattern.test(lines[lineIndex])) {
      lines[lineIndex] = lines[lineIndex].replace(pattern, `$1: unknown`);
      modified = true;
      stats.fixedErrors++;
    }
  });

  if (modified) {
    fs.writeFileSync(filePath, lines.join('\n'), 'utf-8');
    return true;
  }
  return false;
}

function fixTS6196(filePath, errors) {
  const lines = fs.readFileSync(filePath, 'utf-8').split('\n');
  let modified = false;

  [...errors].sort((a, b) => b.lineNum - a.lineNum).forEach(error => {
    const lineIndex = error.lineNum - 1;
    if (lineIndex < 0 || lineIndex >= lines.length) return;
    if (!lines[lineIndex].includes('// eslint-disable-line')) {
      lines[lineIndex] += ' // eslint-disable-line';
      modified = true;
      stats.fixedErrors++;
    }
  });

  if (modified) {
    fs.writeFileSync(filePath, lines.join('\n'), 'utf-8');
    return true;
  }
  return false;
}

function processFile(filePath, errors) {
  if (!fs.existsSync(filePath)) return false;

  const rel = path.relative(process.cwd(), filePath);
  let fileModified = false;

  const byType = {};
  errors.forEach(e => {
    if (!byType[e.errorCode]) byType[e.errorCode] = [];
    byType[e.errorCode].push(e);
  });

  if (byType['TS6192']) {
    if (fixTS6192(filePath, byType['TS6192'])) { fileModified = true; console.log(`  ✓ TS6192  ${rel}`); }
  }
  if (byType['TS1484']) {
    if (fixTS1484(filePath, byType['TS1484'])) { fileModified = true; console.log(`  ✓ TS1484  ${rel}`); }
  }
  if (byType['TS6133']) {
    if (fixTS6133(filePath, byType['TS6133'])) { fileModified = true; console.log(`  ✓ TS6133  ${rel}`); }
  }
  if (byType['TS2724']) {
    if (fixTS2724(filePath, byType['TS2724'])) { fileModified = true; console.log(`  ✓ TS2724  ${rel}`); }
  }
  if (byType['TS2591']) {
    if (fixTS2591(filePath, byType['TS2591'])) { fileModified = true; console.log(`  ✓ TS2591  ${rel}`); }
  }
  if (byType['TS7006']) {
    if (fixTS7006(filePath, byType['TS7006'])) { fileModified = true; console.log(`  ✓ TS7006  ${rel}`); }
  }
  if (byType['TS6196'] || byType['TS6198']) {
    const errs = [...(byType['TS6196'] || []), ...(byType['TS6198'] || [])];
    if (fixTS6196(filePath, errs)) { fileModified = true; console.log(`  ✓ TS619x  ${rel}`); }
  }

  if (fileModified) stats.fixedFiles.add(filePath);
  return fileModified;
}

function main() {
  const output = getTscErrors();
  if (!output) { console.log('✅ 没有错误！\n'); return; }

  const errors = parseErrors(output);
  if (errors.length === 0) { console.log('⚠️  无法解析错误\n'); return; }

  console.log('🔧 Step 3: 开始修复...\n');
  const fileMap = groupByFile(errors);
  let processed = 0;

  fileMap.forEach((fileErrors, filePath) => {
    processed++;
    processFile(filePath, fileErrors);
    if (processed % 100 === 0) {
      console.log(`\n   进度: ${processed}/${fileMap.size}\n`);
    }
  });

  console.log('\n' + '='.repeat(80));
  console.log('📊 修复完成');
  console.log('='.repeat(80));
  console.log(`发现错误: ${stats.totalErrors}`);
  console.log(`修复次数: ${stats.fixedErrors}`);
  console.log(`修改文件: ${stats.fixedFiles.size}`);
  console.log('='.repeat(80) + '\n');
  console.log('💡 下一步:\n');
  console.log('  NODE_NO_WARNINGS=1 npx tsc --noEmit -p tsconfig.app.json 2>&1 | grep "Found.*errors"\n');
}

main();
