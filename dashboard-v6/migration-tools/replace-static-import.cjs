#!/usr/bin/env node

/**
 * 静态方法 Import 替换工具
 * 
 * 功能：将 antd 静态方法的 import 替换为全局单例
 * 使用方法：node replace-static-import.js <目标目录> [--dry-run] [--backup]
 * 
 * 替换规则：
 * - import { message } from 'antd' → import { globalMessage as message } from '@/utils/antd-global'
 * - import { notification } from 'antd' → import { globalNotification as notification } from '@/utils/antd-global'
 * - import { Modal } from 'antd' → import { globalModal as Modal } from '@/utils/antd-global'
 */

const fs = require('fs');
const path = require('path');

// 统计信息
const stats = {
  totalFiles: 0,
  scannedFiles: 0,
  modifiedFiles: 0,
  replacements: {
    message: 0,
    notification: 0,
    Modal: 0,
  },
  errors: [],
};

// 替换详情
const replacementDetails = [];

/**
 * 解析 import 语句，提取导入的模块
 */
function parseImports(content) {
  // 匹配 import { ... } from 'antd'
  const importRegex = /import\s+{([^}]+)}\s+from\s+['"]antd['"]/g;
  const imports = [];
  
  let match;
  while ((match = importRegex.exec(content)) !== null) {
    const fullMatch = match[0];
    const importedItems = match[1]
      .split(',')
      .map(item => item.trim())
      .filter(item => item.length > 0);
    
    imports.push({
      fullMatch,
      start: match.index,
      end: match.index + fullMatch.length,
      items: importedItems,
    });
  }
  
  return imports;
}

/**
 * 生成新的 import 语句
 */
function generateNewImports(importedItems) {
  const targetItems = ['message', 'notification', 'Modal'];
  
  // 分离需要替换的和不需要替换的
  const needReplace = [];
  const keepOriginal = [];
  
  importedItems.forEach(item => {
    // 处理 as 别名的情况: message as msg
    const baseItem = item.split(' as ')[0].trim();
    
    if (targetItems.includes(baseItem)) {
      needReplace.push(item);
    } else {
      keepOriginal.push(item);
    }
  });
  
  const newImports = [];
  
  // 如果有不需要替换的，保留原 import
  if (keepOriginal.length > 0) {
    newImports.push(`import { ${keepOriginal.join(', ')} } from 'antd'`);
  }
  
  // 添加全局单例的 import
  if (needReplace.length > 0) {
    const globalImports = needReplace.map(item => {
      const parts = item.split(' as ');
      const baseItem = parts[0].trim();
      const alias = parts[1] ? parts[1].trim() : baseItem;
      
      const mapping = {
        'message': 'globalMessage',
        'notification': 'globalNotification',
        'Modal': 'globalModal',
      };
      
      return `${mapping[baseItem]} as ${alias}`;
    });
    
    newImports.push(`import { ${globalImports.join(', ')} } from '@/utils/antd-global'`);
  }
  
  return {
    newImports,
    needReplace: needReplace.map(item => item.split(' as ')[0].trim()),
  };
}

/**
 * 处理单个文件
 */
function processFile(filePath, options) {
  let content = fs.readFileSync(filePath, 'utf-8');
  const originalContent = content;
  
  // 解析 import
  const imports = parseImports(content);
  
  if (imports.length === 0) {
    return; // 没有 antd import，跳过
  }
  
  let modified = false;
  const fileReplacements = {
    message: false,
    notification: false,
    Modal: false,
  };
  
  // 从后往前替换，避免索引变化
  imports.reverse().forEach(importInfo => {
    const { fullMatch, start, end, items } = importInfo;
    
    const { newImports, needReplace } = generateNewImports(items);
    
    if (needReplace.length > 0) {
      // 记录替换项
      needReplace.forEach(item => {
        fileReplacements[item] = true;
        stats.replacements[item]++;
      });
      
      // 替换 import 语句
      const before = content.substring(0, start);
      const after = content.substring(end);
      content = before + newImports.join('\n') + after;
      
      modified = true;
    }
  });
  
  if (modified) {
    stats.modifiedFiles++;
    
    replacementDetails.push({
      file: filePath,
      replacements: fileReplacements,
    });
    
    if (options.dryRun) {
      console.log(`\n📝 [DRY RUN] 将修改: ${filePath}`);
      if (fileReplacements.message) console.log('   ✓ message → globalMessage');
      if (fileReplacements.notification) console.log('   ✓ notification → globalNotification');
      if (fileReplacements.Modal) console.log('   ✓ Modal → globalModal');
    } else {
      // 备份
      if (options.backup) {
        fs.writeFileSync(filePath + '.bak', originalContent);
      }
      
      // 写入
      try {
        fs.writeFileSync(filePath, content, 'utf-8');
        console.log(`✅ 已修改: ${filePath}`);
        if (fileReplacements.message) console.log('   ✓ message → globalMessage');
        if (fileReplacements.notification) console.log('   ✓ notification → globalNotification');
        if (fileReplacements.Modal) console.log('   ✓ Modal → globalModal');
      } catch (error) {
        stats.errors.push({ file: filePath, error: error.message });
        console.error(`❌ 修改失败: ${filePath}`);
        console.error(`   错误: ${error.message}`);
      }
    }
  }
}

/**
 * 递归扫描目录
 */
function scanDirectory(dir, options) {
  const files = fs.readdirSync(dir);
  
  files.forEach(file => {
    const filePath = path.join(dir, file);
    const stat = fs.statSync(filePath);
    
    if (stat.isDirectory()) {
      if (!['node_modules', '.git', 'build', 'dist', '.next'].includes(file)) {
        scanDirectory(filePath, options);
      }
    } else if (stat.isFile()) {
      if (/\.(tsx?|jsx?)$/.test(file)) {
        stats.totalFiles++;
        stats.scannedFiles++;
        processFile(filePath, options);
      }
    }
  });
}

/**
 * 生成报告
 */
function generateReport(options) {
  console.log('\n' + '='.repeat(80));
  console.log('📊 静态方法 Import 替换报告');
  if (options.dryRun) {
    console.log('   [DRY RUN 模式 - 未实际修改文件]');
  }
  console.log('='.repeat(80) + '\n');
  
  console.log('📁 文件统计:');
  console.log(`   扫描文件总数: ${stats.totalFiles}`);
  console.log(`   修改文件数: ${stats.modifiedFiles}\n`);
  
  console.log('📋 替换统计:');
  console.log(`   message: ${stats.replacements.message} 处`);
  console.log(`   notification: ${stats.replacements.notification} 处`);
  console.log(`   Modal: ${stats.replacements.Modal} 处`);
  console.log(`   总计: ${Object.values(stats.replacements).reduce((a, b) => a + b, 0)} 处\n`);
  
  if (stats.errors.length > 0) {
    console.log('❌ 错误列表:');
    stats.errors.forEach(({ file, error }) => {
      console.log(`   ${file}`);
      console.log(`      ${error}`);
    });
    console.log('');
  }
  
  console.log('='.repeat(80));
  
  if (options.dryRun) {
    console.log('💡 提示: 这是 DRY RUN 模式，文件未被实际修改');
    console.log('   如果确认无误，请去掉 --dry-run 参数重新运行');
  } else {
    console.log('✅ 替换完成！');
    if (options.backup) {
      console.log('   原文件已备份为 .bak 文件');
    }
    console.log('   建议使用 git diff 检查改动');
  }
  
  console.log('='.repeat(80) + '\n');
  
  // 重要提醒
  if (stats.modifiedFiles > 0) {
    console.log('⚠️ 重要提醒:');
    console.log('   1. 确保已创建 src/utils/antd-global.ts 文件');
    console.log('   2. 确保 AppProvider 已初始化全局实例');
    console.log('   3. 确保根组件包裹了 <App> 组件');
    console.log('   4. 运行项目测试所有 message/notification/Modal 功能');
    console.log('   5. 参考 static-methods-migration.md 了解详细说明\n');
    
    console.log('📝 下一步:');
    console.log('   1. 检查代码改动: git diff');
    console.log('   2. 运行项目: npm start');
    console.log('   3. 测试静态方法是否正常工作');
    console.log('   4. 如有问题，参考 static-methods-migration.md\n');
  }
}

/**
 * 生成 antd-global.ts 模板（如果文件不存在）
 */
function checkAntdGlobalFile(projectRoot) {
  const targetPath = path.join(projectRoot, 'src/utils/antd-global.ts');
  
  if (fs.existsSync(targetPath)) {
    console.log(`✅ 已存在: ${targetPath}\n`);
    return;
  }
  
  console.log(`\n⚠️ 警告: 未找到 src/utils/antd-global.ts 文件`);
  console.log('   这是全局单例工具的核心文件，必须先创建！\n');
  console.log('建议操作:');
  console.log('   1. 运行: node migration-tools/component-template.js --global-utils ./src/utils');
  console.log('   或');
  console.log('   2. 参考 static-methods-migration.md 手动创建\n');
}

/**
 * 主函数
 */
function main() {
  const args = process.argv.slice(2);
  
  if (args.length === 0 || args.includes('--help') || args.includes('-h')) {
    console.log(`
静态方法 Import 替换工具

功能：
  自动将 message/notification/Modal 的 import 替换为全局单例

用法:
  node replace-static-import.js <目标目录> [选项]

选项:
  --dry-run   只显示将要修改的内容，不实际修改文件
  --backup    修改前备份原文件（.bak）
  --help, -h  显示帮助信息

示例:
  # 预览将要修改的内容
  node replace-static-import.js ./src --dry-run

  # 执行替换并备份
  node replace-static-import.js ./src --backup

替换规则:
  import { message } from 'antd'
  → import { globalMessage as message } from '@/utils/antd-global'

  import { notification } from 'antd'
  → import { globalNotification as notification } from '@/utils/antd-global'

  import { Modal } from 'antd'
  → import { globalModal as Modal } from '@/utils/antd-global'

注意事项:
  1. 必须先创建 src/utils/antd-global.ts 文件
  2. 必须在 AppProvider 中初始化全局实例
  3. 参考 static-methods-migration.md 了解详细说明
    `);
    process.exit(0);
  }
  
  const targetDir = args[0];
  const options = {
    dryRun: args.includes('--dry-run'),
    backup: args.includes('--backup'),
  };
  
  if (!fs.existsSync(targetDir)) {
    console.error(`❌ 目录不存在: ${targetDir}`);
    process.exit(1);
  }
  
  console.log(`\n🔧 开始处理目录: ${targetDir}`);
  if (options.dryRun) {
    console.log('   [DRY RUN 模式]');
  }
  if (options.backup) {
    console.log('   [备份模式]');
  }
  
  // 检查 antd-global.ts 文件
  const projectRoot = path.resolve(targetDir, '..');
  checkAntdGlobalFile(projectRoot);
  
  console.log('');
  
  scanDirectory(targetDir, options);
  generateReport(options);
}

main();
