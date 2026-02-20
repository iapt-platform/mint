#!/usr/bin/env node

/**
 * Ant Design v4 → v6 Import 语句批量替换工具
 * 
 * 功能：自动替换代码中的 antd API 调用
 * 使用方法：node import-replace.js <目标目录路径> [--dry-run] [--backup]
 * 
 * 参数说明：
 *   --dry-run  : 只显示将要修改的内容，不实际修改文件
 *   --backup   : 修改前备份原文件（.bak）
 */

const fs = require('fs');
const path = require('path');

// 替换规则配置
const replacements = [
  // 1. visible → open
  {
    name: 'visible to open (props)',
    pattern: /(\s+)visible(\s*=\s*\{)/g,
    replacement: '$1open$2',
    filePattern: /\.(tsx?|jsx?)$/,
    description: 'Modal/Drawer/Popover 等组件的 visible 改为 open'
  },
  {
    name: 'onVisibleChange to onOpenChange',
    pattern: /(\s+)onVisibleChange(\s*=\s*\{)/g,
    replacement: '$1onOpenChange$2',
    filePattern: /\.(tsx?|jsx?)$/,
    description: 'onVisibleChange 改为 onOpenChange'
  },
  
  // 2. moment → dayjs
  {
    name: 'moment import to dayjs',
    pattern: /import\s+moment\s+from\s+['"]moment['"]/g,
    replacement: "import dayjs from 'dayjs'",
    filePattern: /\.(tsx?|jsx?)$/,
    description: 'moment.js import 改为 dayjs'
  },
  {
    name: 'moment() to dayjs()',
    pattern: /\bmoment\(/g,
    replacement: 'dayjs(',
    filePattern: /\.(tsx?|jsx?)$/,
    description: 'moment() 调用改为 dayjs()'
  },
  
  // 3. Dropdown overlay → menu
  {
    name: 'Dropdown overlay to menu',
    pattern: /(\s+)overlay(\s*=\s*\{)/g,
    replacement: '$1menu$2',
    filePattern: /\.(tsx?|jsx?)$/,
    description: 'Dropdown 的 overlay 改为 menu',
    warning: '⚠️ 注意：menu 需要返回 Menu 组件，可能需要手动调整'
  },
  
  // 4. PageHeader → 需要手动处理，这里只标记
  // 不做自动替换，因为需要手动迁移到 ProComponents
  
  // 5. BackTop → FloatButton.BackTop
  {
    name: 'BackTop to FloatButton.BackTop',
    pattern: /<BackTop(\s+[^>]*)?>/g,
    replacement: '<FloatButton.BackTop$1>',
    filePattern: /\.(tsx?|jsx?)$/,
    description: 'BackTop 改为 FloatButton.BackTop',
    warning: '⚠️ 注意：需要确保已导入 FloatButton'
  },
  {
    name: 'BackTop closing tag',
    pattern: /<\/BackTop>/g,
    replacement: '</FloatButton.BackTop>',
    filePattern: /\.(tsx?|jsx?)$/,
    description: 'BackTop 结束标签'
  },
  
  // 6. 添加必要的 import (如果文件中使用了 FloatButton 但未导入)
  // 这个需要更智能的处理，暂时跳过
];

// 统计信息
const stats = {
  totalFiles: 0,
  modifiedFiles: 0,
  totalReplacements: 0,
  replacementsByType: new Map(),
  errors: []
};

// 递归扫描目录
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
        processFile(filePath, options);
      }
    }
  });
}

// 处理单个文件
function processFile(filePath, options) {
  let content = fs.readFileSync(filePath, 'utf-8');
  const originalContent = content;
  let fileModified = false;
  let fileReplacements = [];
  
  replacements.forEach(rule => {
    if (!rule.filePattern.test(filePath)) {
      return;
    }
    
    const matches = content.match(rule.pattern);
    if (matches && matches.length > 0) {
      content = content.replace(rule.pattern, rule.replacement);
      
      const count = matches.length;
      fileReplacements.push({
        rule: rule.name,
        count,
        description: rule.description,
        warning: rule.warning
      });
      
      stats.totalReplacements += count;
      stats.replacementsByType.set(
        rule.name,
        (stats.replacementsByType.get(rule.name) || 0) + count
      );
      
      fileModified = true;
    }
  });
  
  if (fileModified) {
    stats.modifiedFiles++;
    
    if (options.dryRun) {
      console.log(`\n📝 [DRY RUN] 将修改: ${filePath}`);
      fileReplacements.forEach(rep => {
        console.log(`   ✓ ${rep.description} (${rep.count} 处)`);
        if (rep.warning) {
          console.log(`     ${rep.warning}`);
        }
      });
    } else {
      // 备份原文件
      if (options.backup) {
        fs.writeFileSync(filePath + '.bak', originalContent);
      }
      
      // 写入修改后的内容
      try {
        fs.writeFileSync(filePath, content, 'utf-8');
        console.log(`✅ 已修改: ${filePath}`);
        fileReplacements.forEach(rep => {
          console.log(`   ✓ ${rep.description} (${rep.count} 处)`);
          if (rep.warning) {
            console.log(`     ${rep.warning}`);
          }
        });
      } catch (error) {
        stats.errors.push({ file: filePath, error: error.message });
        console.error(`❌ 修改失败: ${filePath}`);
        console.error(`   错误: ${error.message}`);
      }
    }
  }
}

// 生成报告
function generateReport(options) {
  console.log('\n' + '='.repeat(80));
  console.log('📊 Ant Design v4 → v6 批量替换报告');
  if (options.dryRun) {
    console.log('   [DRY RUN 模式 - 未实际修改文件]');
  }
  console.log('='.repeat(80) + '\n');
  
  console.log('📁 文件统计:');
  console.log(`   扫描文件总数: ${stats.totalFiles}`);
  console.log(`   修改文件数: ${stats.modifiedFiles}`);
  console.log(`   替换总次数: ${stats.totalReplacements}\n`);
  
  if (stats.replacementsByType.size > 0) {
    console.log('📋 替换详情:');
    const sorted = Array.from(stats.replacementsByType.entries())
      .sort((a, b) => b[1] - a[1]);
    
    sorted.forEach(([rule, count]) => {
      console.log(`   ${rule}: ${count} 次`);
    });
    console.log('');
  }
  
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
    console.log('   建议使用 git diff 检查改动，确保无误后再提交');
  }
  
  console.log('='.repeat(80) + '\n');
  
  // 特别提醒
  if (stats.totalReplacements > 0) {
    console.log('⚠️ 重要提醒:');
    console.log('   1. 某些替换可能需要手动调整（如 Dropdown 的 menu 属性）');
    console.log('   2. 建议运行 scan-api-diff.js 再次检查是否有遗漏');
    console.log('   3. 必须手动处理以下情况:');
    console.log('      - PageHeader 组件（已移除，需迁移到 ProComponents）');
    console.log('      - message/notification 静态方法（需要 App.useApp()）');
    console.log('      - Modal.confirm 等静态方法（需要 Modal.useModal()）');
    console.log('      - Form.getFieldDecorator（已废弃）\n');
  }
}

// 主函数
function main() {
  const args = process.argv.slice(2);
  
  if (args.length === 0 || args.includes('--help') || args.includes('-h')) {
    console.log(`
Ant Design v4 → v6 Import 批量替换工具

用法:
  node import-replace.js <目标目录> [选项]

选项:
  --dry-run   只显示将要修改的内容，不实际修改文件
  --backup    修改前备份原文件（.bak）
  --help, -h  显示帮助信息

示例:
  # 预览将要修改的内容
  node import-replace.js ../old-project/src --dry-run

  # 执行替换并备份
  node import-replace.js ../old-project/src --backup

  # 直接执行替换（谨慎使用）
  node import-replace.js ../old-project/src
    `);
    process.exit(0);
  }
  
  const targetDir = args[0];
  const options = {
    dryRun: args.includes('--dry-run'),
    backup: args.includes('--backup')
  };
  
  if (!fs.existsSync(targetDir)) {
    console.error(`❌ 目录不存在: ${targetDir}`);
    process.exit(1);
  }
  
  if (!options.dryRun && !options.backup) {
    console.log('⚠️ 警告: 您没有启用备份选项，文件将直接被修改！');
    console.log('建议先使用 --dry-run 预览，或添加 --backup 备份原文件\n');
  }
  
  console.log(`\n🔧 开始处理目录: ${targetDir}`);
  if (options.dryRun) {
    console.log('   [DRY RUN 模式]');
  }
  if (options.backup) {
    console.log('   [备份模式]');
  }
  console.log('');
  
  scanDirectory(targetDir, options);
  generateReport(options);
}

main();
