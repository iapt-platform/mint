const fs = require('fs');
const path = require('path');

const filePath = path.resolve(__dirname, '../src/components/anthology/AnthologyList.tsx');
console.log('测试文件:', filePath);
console.log('文件存在:', fs.existsSync(filePath));

if (fs.existsSync(filePath)) {
  const lines = fs.readFileSync(filePath, 'utf-8').split('\n');
  console.log('\n第 21 行 (索引 20):');
  console.log('内容:', JSON.stringify(lines[20]));
  console.log('长度:', lines[20].length);
  
  // 测试匹配
  const pattern = /^(\s*)import\s+(\w+)\s*,\s*\{([^}]+)\}\s*from\s*(['"][^'"]+['"])\s*;?\s*$/;
  const match = lines[20].match(pattern);
  console.log('\n匹配结果:', match ? '✅ 成功' : '❌ 失败');
  if (match) {
    console.log('捕获:', {
      indent: match[1],
      defaultName: match[2],
      imports: match[3],
      source: match[4],
    });
  }
  
  // 模拟脚本的查找逻辑
  console.log('\n=== 模拟脚本查找 ===');
  const errorLineNum = 21;
  const errorLineIndex = errorLineNum - 1; // 20
  
  let startIndex = -1;
  for (let i = errorLineIndex; i >= Math.max(0, errorLineIndex - 30); i--) {
    console.log(`检查第 ${i+1} 行:`, lines[i].substring(0, 50));
    if (lines[i].match(/^\s*import\s/)) {
      startIndex = i;
      console.log(`  → 找到 import 起始: 第 ${i+1} 行`);
      break;
    }
    if (i < errorLineIndex && lines[i].trim() === '') {
      console.log('  → 遇到空行，停止查找');
      break;
    }
  }
  
  console.log('\n最终找到的起始行:', startIndex === -1 ? '未找到' : `第 ${startIndex + 1} 行`);
  if (startIndex !== -1) {
    console.log('起始行内容:', lines[startIndex]);
  }
}
