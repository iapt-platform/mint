#!/usr/bin/env node

/**
 * Ant Design v4 → v6 组件 API 差异扫描器
 * 
 * 功能：扫描代码中使用的 antd v4 API，标记需要修改的地方
 * 使用方法：node scan-api-diff.js <目标目录路径>
 */

const fs = require('fs');
const path = require('path');

// v4 → v6 需要修改的 API 模式
const patterns = [
  // Modal, Drawer, Popover 等组件的 visible 改为 open
  {
    pattern: /visible\s*=\s*\{/g,
    suggestion: '❌ 使用了 visible 属性',
    fix: '将 visible 改为 open',
    severity: 'high',
    component: 'Modal/Drawer/Popover/Tooltip/Dropdown'
  },
  {
    pattern: /onVisibleChange\s*=\s*\{/g,
    suggestion: '❌ 使用了 onVisibleChange 属性',
    fix: '将 onVisibleChange 改为 onOpenChange',
    severity: 'high',
    component: 'Modal/Drawer/Popover/Tooltip/Dropdown'
  },
  
  // 静态方法调用（message, notification, Modal）
  {
    pattern: /message\.(success|error|info|warning|loading)\(/g,
    suggestion: '⚠️ 直接调用静态方法',
    fix: '改用 App.useApp() hook 或确保有 <App> 包裹',
    severity: 'medium',
    component: 'message'
  },
  {
    pattern: /notification\.(success|error|info|warning|open)\(/g,
    suggestion: '⚠️ 直接调用静态方法',
    fix: '改用 App.useApp() hook 或确保有 <App> 包裹',
    severity: 'medium',
    component: 'notification'
  },
  {
    pattern: /Modal\.(confirm|info|success|error|warning)\(/g,
    suggestion: '⚠️ 直接调用静态方法',
    fix: '改用 Modal.useModal() hook 或确保有 <App> 包裹',
    severity: 'medium',
    component: 'Modal'
  },
  
  // moment.js 使用
  {
    pattern: /import\s+.*moment.*from\s+['"]moment['"]/g,
    suggestion: '❌ 使用了 moment.js',
    fix: '替换为 dayjs',
    severity: 'high',
    component: 'DatePicker/TimePicker/Calendar'
  },
  {
    pattern: /moment\(/g,
    suggestion: '❌ 调用了 moment()',
    fix: '替换为 dayjs()',
    severity: 'high',
    component: 'DatePicker/TimePicker/Calendar'
  },
  
  // Form 相关（部分需要调整）
  {
    pattern: /getFieldDecorator/g,
    suggestion: '❌ 使用了旧版 Form API',
    fix: 'v4+ 已废弃，使用 Form.Item name 属性',
    severity: 'critical',
    component: 'Form'
  },
  
  // Dropdown overlay 改为 menu
  {
    pattern: /overlay\s*=\s*\{/g,
    suggestion: '⚠️ Dropdown 使用了 overlay',
    fix: '将 overlay 改为 menu (返回 Menu 组件)',
    severity: 'medium',
    component: 'Dropdown'
  },
  
  // PageHeader 已移除
  {
    pattern: /<PageHeader/g,
    suggestion: '❌ PageHeader 组件已移除',
    fix: '使用 @ant-design/pro-components 的 PageContainer 或自定义',
    severity: 'high',
    component: 'PageHeader'
  },
  
  // Comment 组件已移除
  {
    pattern: /<Comment/g,
    suggestion: '❌ Comment 组件已移除',
    fix: '使用 @ant-design/compatible 或自定义',
    severity: 'medium',
    component: 'Comment'
  },
  
  // BackTop 改为 FloatButton.BackTop
  {
    pattern: /<BackTop/g,
    suggestion: '⚠️ BackTop 组件 API 有变更',
    fix: '使用 FloatButton.BackTop',
    severity: 'low',
    component: 'BackTop'
  },
  
  // Less 变量
  {
    pattern: /@import\s+['"]~antd\/.*\.less['"]/g,
    suggestion: '⚠️ 引入了 antd less 文件',
    fix: '改用 ConfigProvider theme 配置',
    severity: 'medium',
    component: 'Theme'
  },
  {
    pattern: /@primary-color|@link-color|@success-color|@warning-color|@error-color/g,
    suggestion: '⚠️ 使用了 less 变量',
    fix: '改用 Design Token',
    severity: 'medium',
    component: 'Theme'
  },
];

// 扫描结果统计
const stats = {
  totalFiles: 0,
  scannedFiles: 0,
  filesWithIssues: 0,
  issues: {
    critical: 0,
    high: 0,
    medium: 0,
    low: 0
  }
};

// 问题汇总
const issuesByFile = new Map();

// 递归扫描目录
function scanDirectory(dir, baseDir) {
  const files = fs.readdirSync(dir);
  
  files.forEach(file => {
    const filePath = path.join(dir, file);
    const stat = fs.statSync(filePath);
    
    if (stat.isDirectory()) {
      // 跳过 node_modules 等目录
      if (!['node_modules', '.git', 'build', 'dist', '.next'].includes(file)) {
        scanDirectory(filePath, baseDir);
      }
    } else if (stat.isFile()) {
      // 只扫描 tsx, ts, jsx, js 文件
      if (/\.(tsx?|jsx?)$/.test(file)) {
        stats.totalFiles++;
        scanFile(filePath, baseDir);
      }
    }
  });
}

// 扫描单个文件
function scanFile(filePath, baseDir) {
  const content = fs.readFileSync(filePath, 'utf-8');
  const relativePath = path.relative(baseDir, filePath);
  
  stats.scannedFiles++;
  
  const fileIssues = [];
  
  patterns.forEach(({ pattern, suggestion, fix, severity, component }) => {
    const matches = content.matchAll(pattern);
    
    for (const match of matches) {
      // 计算行号
      const lines = content.substring(0, match.index).split('\n');
      const lineNumber = lines.length;
      const columnNumber = lines[lines.length - 1].length + 1;
      
      fileIssues.push({
        line: lineNumber,
        column: columnNumber,
        code: match[0],
        suggestion,
        fix,
        severity,
        component
      });
      
      stats.issues[severity]++;
    }
  });
  
  if (fileIssues.length > 0) {
    stats.filesWithIssues++;
    issuesByFile.set(relativePath, fileIssues);
  }
}

// 生成报告
function generateReport() {
  console.log('\n' + '='.repeat(80));
  console.log('📊 Ant Design v4 → v6 API 差异扫描报告');
  console.log('='.repeat(80) + '\n');
  
  console.log('📁 扫描统计:');
  console.log(`   总文件数: ${stats.totalFiles}`);
  console.log(`   已扫描: ${stats.scannedFiles}`);
  console.log(`   有问题的文件: ${stats.filesWithIssues}\n`);
  
  console.log('🚨 问题统计:');
  console.log(`   🔴 Critical: ${stats.issues.critical} 个`);
  console.log(`   🟠 High: ${stats.issues.high} 个`);
  console.log(`   🟡 Medium: ${stats.issues.medium} 个`);
  console.log(`   🟢 Low: ${stats.issues.low} 个\n`);
  
  console.log('='.repeat(80) + '\n');
  
  if (issuesByFile.size === 0) {
    console.log('✅ 未发现明显的兼容性问题！\n');
    return;
  }
  
  console.log('📋 详细问题列表:\n');
  
  // 按严重程度排序
  const severityOrder = { critical: 0, high: 1, medium: 2, low: 3 };
  const sortedFiles = Array.from(issuesByFile.entries()).sort((a, b) => {
    const minSeverityA = Math.min(...a[1].map(i => severityOrder[i.severity]));
    const minSeverityB = Math.min(...b[1].map(i => severityOrder[i.severity]));
    return minSeverityA - minSeverityB;
  });
  
  sortedFiles.forEach(([file, issues]) => {
    const severityIcon = {
      critical: '🔴',
      high: '🟠',
      medium: '🟡',
      low: '🟢'
    };
    
    console.log(`📄 ${file}`);
    console.log('   ' + '-'.repeat(76));
    
    issues.forEach(issue => {
      console.log(`   ${severityIcon[issue.severity]} Line ${issue.line}:${issue.column} [${issue.component}]`);
      console.log(`      ${issue.suggestion}`);
      console.log(`      代码: ${issue.code}`);
      console.log(`      💡 修复建议: ${issue.fix}`);
      console.log('');
    });
  });
  
  console.log('='.repeat(80) + '\n');
  
  // 按组件分组统计
  console.log('📊 按组件分类统计:\n');
  const componentStats = new Map();
  
  issuesByFile.forEach(issues => {
    issues.forEach(issue => {
      const count = componentStats.get(issue.component) || 0;
      componentStats.set(issue.component, count + 1);
    });
  });
  
  const sortedComponents = Array.from(componentStats.entries())
    .sort((a, b) => b[1] - a[1]);
  
  sortedComponents.forEach(([component, count]) => {
    console.log(`   ${component}: ${count} 个问题`);
  });
  
  console.log('\n' + '='.repeat(80));
  console.log('💡 建议:');
  console.log('   1. 优先处理 🔴 Critical 和 🟠 High 级别的问题');
  console.log('   2. 使用 import-replace.js 脚本批量替换简单的 API');
  console.log('   3. 参考 migration-checklist.md 了解详细迁移步骤');
  console.log('='.repeat(80) + '\n');
}

// 导出为 JSON 格式（可选）
function exportJson(outputPath) {
  const result = {
    timestamp: new Date().toISOString(),
    stats,
    issues: Object.fromEntries(issuesByFile)
  };
  
  fs.writeFileSync(outputPath, JSON.stringify(result, null, 2));
  console.log(`\n📝 详细报告已导出到: ${outputPath}\n`);
}

// 主函数
function main() {
  const args = process.argv.slice(2);
  
  if (args.length === 0) {
    console.log('用法: node scan-api-diff.js <目标目录路径> [--json=输出文件.json]');
    console.log('示例: node scan-api-diff.js ../old-project/src --json=report.json');
    process.exit(1);
  }
  
  const targetDir = args[0];
  const jsonOutput = args.find(arg => arg.startsWith('--json='))?.split('=')[1];
  
  if (!fs.existsSync(targetDir)) {
    console.error(`❌ 目录不存在: ${targetDir}`);
    process.exit(1);
  }
  
  console.log(`\n🔍 开始扫描目录: ${targetDir}\n`);
  
  scanDirectory(targetDir, targetDir);
  generateReport();
  
  if (jsonOutput) {
    exportJson(jsonOutput);
  }
}

main();
