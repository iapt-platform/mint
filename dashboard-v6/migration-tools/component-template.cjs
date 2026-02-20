#!/usr/bin/env node

/**
 * Ant Design v6 组件迁移模板生成器
 * 
 * 功能：生成符合 v6 最佳实践的组件模板
 * 使用方法：node component-template.js <组件类型> <组件名>
 */

const fs = require('fs');
const path = require('path');

// 组件模板
const templates = {
  // 基础功能组件
  basic: (componentName) => `import React from 'react';
import { Card, Button } from 'antd';

interface ${componentName}Props {
  // TODO: 添加你的 props 类型
}

/**
 * ${componentName} 组件
 * 
 * v4 → v6 迁移说明：
 * - 已更新到 v6 API
 * - 使用新的类型定义
 */
const ${componentName}: React.FC<${componentName}Props> = (props) => {
  return (
    <Card title="${componentName}">
      {/* TODO: 实现你的组件逻辑 */}
    </Card>
  );
};

export default ${componentName};
`,

  // 表单组件
  form: (componentName) => `import React from 'react';
import { Form, Input, Button, message, App } from 'antd';
import type { FormProps } from 'antd';

interface ${componentName}FormValues {
  // TODO: 添加表单字段类型
  name?: string;
}

interface ${componentName}Props {
  onSubmit?: (values: ${componentName}FormValues) => void;
}

/**
 * ${componentName} 表单组件
 * 
 * v4 → v6 迁移说明：
 * - ✅ 使用 App.useApp() 获取 message 实例
 * - ✅ Form API 已更新到 v6
 * - ✅ 使用 TypeScript 类型定义
 */
const ${componentName}: React.FC<${componentName}Props> = ({ onSubmit }) => {
  const [form] = Form.useForm<${componentName}FormValues>();
  const { message } = App.useApp();

  const handleFinish: FormProps<${componentName}FormValues>['onFinish'] = async (values) => {
    try {
      await onSubmit?.(values);
      message.success('提交成功');
      form.resetFields();
    } catch (error) {
      message.error('提交失败');
      console.error(error);
    }
  };

  return (
    <Form
      form={form}
      layout="vertical"
      onFinish={handleFinish}
      autoComplete="off"
    >
      <Form.Item
        label="名称"
        name="name"
        rules={[{ required: true, message: '请输入名称' }]}
      >
        <Input placeholder="请输入名称" />
      </Form.Item>

      {/* TODO: 添加更多表单项 */}

      <Form.Item>
        <Button type="primary" htmlType="submit">
          提交
        </Button>
      </Form.Item>
    </Form>
  );
};

export default ${componentName};
`,

  // 带 Modal 的组件
  modal: (componentName) => `import React, { useState } from 'react';
import { Modal, Button, App } from 'antd';
import type { ModalProps } from 'antd';

interface ${componentName}Props {
  title?: string;
  onConfirm?: () => Promise<void>;
}

/**
 * ${componentName} Modal 组件
 * 
 * v4 → v6 迁移说明：
 * - ✅ visible 改为 open
 * - ✅ onVisibleChange 改为 onOpenChange
 * - ✅ 使用 App.useApp() 获取 message
 */
const ${componentName}: React.FC<${componentName}Props> = ({ 
  title = '${componentName}',
  onConfirm 
}) => {
  const [open, setOpen] = useState(false);
  const [loading, setLoading] = useState(false);
  const { message } = App.useApp();

  const handleOpen = () => setOpen(true);
  
  const handleCancel = () => {
    if (!loading) {
      setOpen(false);
    }
  };

  const handleOk = async () => {
    setLoading(true);
    try {
      await onConfirm?.();
      message.success('操作成功');
      setOpen(false);
    } catch (error) {
      message.error('操作失败');
      console.error(error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <Button type="primary" onClick={handleOpen}>
        打开 {title}
      </Button>
      
      <Modal
        title={title}
        open={open}
        onOk={handleOk}
        onCancel={handleCancel}
        confirmLoading={loading}
        destroyOnClose
      >
        {/* TODO: 添加 Modal 内容 */}
        <p>Modal 内容</p>
      </Modal>
    </>
  );
};

export default ${componentName};
`,

  // 带 Table 的组件
  table: (componentName) => `import React, { useState, useEffect } from 'react';
import { Table, Button, Space, App } from 'antd';
import type { TableProps } from 'antd';

interface DataType {
  key: string;
  // TODO: 添加你的数据类型
  name: string;
}

interface ${componentName}Props {
  // TODO: 添加 props
}

/**
 * ${componentName} Table 组件
 * 
 * v4 → v6 迁移说明：
 * - ✅ Table API 已更新到 v6
 * - ✅ 使用 TypeScript 类型定义
 * - ✅ 分页配置已更新
 */
const ${componentName}: React.FC<${componentName}Props> = (props) => {
  const [loading, setLoading] = useState(false);
  const [dataSource, setDataSource] = useState<DataType[]>([]);
  const [pagination, setPagination] = useState({
    current: 1,
    pageSize: 10,
    total: 0,
  });
  const { message } = App.useApp();

  const columns: TableProps<DataType>['columns'] = [
    {
      title: '名称',
      dataIndex: 'name',
      key: 'name',
    },
    // TODO: 添加更多列
    {
      title: '操作',
      key: 'action',
      render: (_, record) => (
        <Space>
          <Button type="link" size="small">
            编辑
          </Button>
          <Button type="link" size="small" danger>
            删除
          </Button>
        </Space>
      ),
    },
  ];

  const fetchData = async (page = 1, pageSize = 10) => {
    setLoading(true);
    try {
      // TODO: 实现数据获取逻辑
      const response = { data: [], total: 0 };
      
      setDataSource(response.data);
      setPagination({
        current: page,
        pageSize,
        total: response.total,
      });
    } catch (error) {
      message.error('获取数据失败');
      console.error(error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const handleTableChange: TableProps<DataType>['onChange'] = (
    pagination,
    filters,
    sorter
  ) => {
    fetchData(pagination.current, pagination.pageSize);
  };

  return (
    <Table
      columns={columns}
      dataSource={dataSource}
      loading={loading}
      pagination={pagination}
      onChange={handleTableChange}
      rowKey="key"
    />
  );
};

export default ${componentName};
`,

  // ProTable 组件
  proTable: (componentName) => `import React, { useRef } from 'react';
import { ProTable } from '@ant-design/pro-components';
import type { ProColumns, ActionType } from '@ant-design/pro-components';
import { Button, Space, App } from 'antd';

interface DataType {
  id: string;
  // TODO: 添加你的数据类型
  name: string;
  createdAt: string;
}

/**
 * ${componentName} ProTable 组件
 * 
 * v4 → v6 迁移说明：
 * - ✅ ProTable API 已更新
 * - ✅ request 函数签名保持兼容
 * - ✅ 使用新的类型定义
 */
const ${componentName}: React.FC = () => {
  const actionRef = useRef<ActionType>();
  const { message } = App.useApp();

  const columns: ProColumns<DataType>[] = [
    {
      title: '名称',
      dataIndex: 'name',
      key: 'name',
    },
    {
      title: '创建时间',
      dataIndex: 'createdAt',
      key: 'createdAt',
      valueType: 'dateTime',
    },
    // TODO: 添加更多列
    {
      title: '操作',
      key: 'action',
      valueType: 'option',
      render: (_, record) => [
        <Button key="edit" type="link" size="small">
          编辑
        </Button>,
        <Button key="delete" type="link" size="small" danger>
          删除
        </Button>,
      ],
    },
  ];

  const handleRequest = async (
    params: any,
    sort: any,
    filter: any
  ) => {
    try {
      // TODO: 实现数据获取逻辑
      const response = {
        data: [],
        total: 0,
        success: true,
      };
      
      return response;
    } catch (error) {
      message.error('获取数据失败');
      return {
        data: [],
        total: 0,
        success: false,
      };
    }
  };

  return (
    <ProTable<DataType>
      columns={columns}
      actionRef={actionRef}
      request={handleRequest}
      rowKey="id"
      search={{
        labelWidth: 'auto',
      }}
      pagination={{
        defaultPageSize: 10,
        showSizeChanger: true,
      }}
      dateFormatter="string"
      headerTitle="${componentName}"
      toolBarRender={() => [
        <Button key="button" type="primary">
          新建
        </Button>,
      ]}
    />
  );
};

export default ${componentName};
`,

  // 主题切换组件（特殊模板）
  theme: () => `import React, { useState, useEffect } from 'react';
import { Switch, Space, Typography } from 'antd';
import { MoonOutlined, SunOutlined } from '@ant-design/icons';

const { Text } = Typography;

type ThemeMode = 'light' | 'dark';

interface ThemeSwitchProps {
  onChange?: (theme: ThemeMode) => void;
}

/**
 * 主题切换组件
 * 
 * v4 → v6 迁移说明：
 * - ❌ 废弃了 CSS 文件方式 (theme/antd.dark.css)
 * - ✅ 使用 ConfigProvider theme 配置
 * - ✅ 支持动态主题切换
 * - ✅ 使用 Design Token
 * 
 * 使用方法：
 * 1. 在 App 根组件中使用 ConfigProvider
 * 2. 根据此组件的状态切换 theme.algorithm
 */
const ThemeSwitch: React.FC<ThemeSwitchProps> = ({ onChange }) => {
  const [theme, setTheme] = useState<ThemeMode>('light');

  useEffect(() => {
    // 从 localStorage 读取保存的主题
    const savedTheme = localStorage.getItem('theme') as ThemeMode;
    if (savedTheme) {
      setTheme(savedTheme);
      onChange?.(savedTheme);
    }
  }, []);

  const handleChange = (checked: boolean) => {
    const newTheme: ThemeMode = checked ? 'dark' : 'light';
    setTheme(newTheme);
    localStorage.setItem('theme', newTheme);
    onChange?.(newTheme);
  };

  return (
    <Space align="center">
      <SunOutlined style={{ color: theme === 'light' ? '#1890ff' : '#999' }} />
      <Switch
        checked={theme === 'dark'}
        onChange={handleChange}
        checkedChildren={<MoonOutlined />}
        unCheckedChildren={<SunOutlined />}
      />
      <Text type="secondary">{theme === 'dark' ? '暗黑' : '明亮'}</Text>
    </Space>
  );
};

export default ThemeSwitch;
`,
};

// App Provider 模板
const appProviderTemplate = () => `import React, { useState } from 'react';
import { ConfigProvider, App as AntdApp, theme } from 'antd';
import zhCN from 'antd/locale/zh_CN';
import ThemeSwitch from './components/ThemeSwitch';

type ThemeMode = 'light' | 'dark';

/**
 * App Provider 组件
 * 
 * v4 → v6 主题系统迁移：
 * - ✅ 使用 ConfigProvider 管理全局配置
 * - ✅ 使用 theme.algorithm 切换主题
 * - ✅ 使用 Design Token 自定义主题
 * - ✅ 使用 App 组件提供全局 message/notification/modal
 */
const AppProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [themeMode, setThemeMode] = useState<ThemeMode>('light');

  return (
    <ConfigProvider
      locale={zhCN}
      theme={{
        // 主题算法：暗黑或明亮
        algorithm: themeMode === 'dark' ? theme.darkAlgorithm : theme.defaultAlgorithm,
        
        // 全局 Design Token
        token: {
          colorPrimary: '#1890ff',
          borderRadius: 6,
          // TODO: 添加更多自定义 token
        },
        
        // 组件级别的主题定制
        components: {
          Button: {
            // TODO: 自定义 Button 样式
          },
          Card: {
            // TODO: 自定义 Card 样式
          },
          // TODO: 添加更多组件定制
        },
      }}
    >
      <AntdApp>
        {/* 主题切换器（可以放在布局的任意位置） */}
        <div style={{ position: 'fixed', top: 16, right: 16, zIndex: 1000 }}>
          <ThemeSwitch onChange={setThemeMode} />
        </div>
        
        {children}
      </AntdApp>
    </ConfigProvider>
  );
};

export default AppProvider;
`;

// 主题 Token 配置模板
const themeTokensTemplate = () => `import { ThemeConfig } from 'antd';

/**
 * 自定义主题 Token 配置
 * 
 * Design Token 说明：
 * - token: 全局 token，影响所有组件
 * - components: 组件级别的 token，只影响特定组件
 * 
 * 常用 Token:
 * - colorPrimary: 品牌主色
 * - colorSuccess: 成功色
 * - colorWarning: 警告色
 * - colorError: 错误色
 * - colorInfo: 信息色
 * - colorText: 文本色
 * - colorBgContainer: 容器背景色
 * - borderRadius: 圆角大小
 * - fontSize: 字体大小
 * 
 * 完整 Token 列表：
 * https://ant.design/docs/react/customize-theme#theme
 */

export const lightTheme: ThemeConfig = {
  token: {
    colorPrimary: '#1890ff',
    colorSuccess: '#52c41a',
    colorWarning: '#faad14',
    colorError: '#ff4d4f',
    colorInfo: '#1890ff',
    
    borderRadius: 6,
    fontSize: 14,
    
    // TODO: 添加更多自定义 token
  },
  components: {
    Button: {
      controlHeight: 32,
      borderRadius: 6,
    },
    Card: {
      borderRadiusLG: 8,
    },
    // TODO: 添加更多组件定制
  },
};

export const darkTheme: ThemeConfig = {
  token: {
    ...lightTheme.token,
    // 暗黑模式可以覆盖特定的 token
    // 注意：使用 theme.darkAlgorithm 会自动处理大部分颜色
  },
  components: lightTheme.components,
};
`;

// 生成组件文件
function generateComponent(type, componentName, outputDir = '.') {
  const template = templates[type];
  
  if (!template) {
    console.error(`❌ 未知的组件类型: ${type}`);
    console.log(`可用类型: ${Object.keys(templates).join(', ')}`);
    process.exit(1);
  }
  
  const content = template(componentName);
  const filename = `${componentName}.tsx`;
  const filepath = path.join(outputDir, filename);
  
  // 检查文件是否已存在
  if (fs.existsSync(filepath)) {
    console.log(`⚠️ 文件已存在: ${filepath}`);
    console.log('是否覆盖？（请手动确认）');
    return;
  }
  
  fs.writeFileSync(filepath, content);
  console.log(`✅ 已生成组件: ${filepath}`);
}

// 生成主题相关文件
function generateThemeFiles(outputDir = '.') {
  const files = [
    { name: 'ThemeSwitch.tsx', content: templates.theme() },
    { name: 'AppProvider.tsx', content: appProviderTemplate() },
    { name: 'tokens.ts', content: themeTokensTemplate() },
  ];
  
  files.forEach(({ name, content }) => {
    const filepath = path.join(outputDir, name);
    
    if (fs.existsSync(filepath)) {
      console.log(`⚠️ 文件已存在: ${filepath} (跳过)`);
      return;
    }
    
    fs.writeFileSync(filepath, content);
    console.log(`✅ 已生成: ${filepath}`);
  });
  
  console.log('\n📝 使用说明:');
  console.log('1. 在你的根组件中引入 AppProvider:');
  console.log('   import AppProvider from "./AppProvider";');
  console.log('   <AppProvider><App /></AppProvider>');
  console.log('');
  console.log('2. 在任意需要使用 message/modal 的组件中:');
  console.log('   const { message } = App.useApp();');
  console.log('');
  console.log('3. 主题切换器已集成在 AppProvider 中');
  console.log('   你可以根据需要调整其位置\n');
}

// 主函数
function main() {
  const args = process.argv.slice(2);
  
  if (args.length === 0 || args.includes('--help') || args.includes('-h')) {
    console.log(`
Ant Design v6 组件模板生成器

用法:
  node component-template.js <类型> <组件名> [输出目录]
  node component-template.js --theme [输出目录]

组件类型:
  basic      - 基础组件
  form       - 表单组件
  modal      - Modal 组件
  table      - Table 组件
  proTable   - ProTable 组件

特殊命令:
  --theme    - 生成完整的主题系统文件（ThemeSwitch + AppProvider + tokens）

示例:
  # 生成基础组件
  node component-template.js basic UserCard ./src/components

  # 生成表单组件
  node component-template.js form UserForm ./src/components

  # 生成主题系统文件
  node component-template.js --theme ./src/theme

可用模板:
  ${Object.keys(templates).join(', ')}
    `);
    process.exit(0);
  }
  
  if (args[0] === '--theme') {
    const outputDir = args[1] || '.';
    console.log(`\n🎨 生成主题系统文件到: ${outputDir}\n`);
    
    if (!fs.existsSync(outputDir)) {
      fs.mkdirSync(outputDir, { recursive: true });
    }
    
    generateThemeFiles(outputDir);
    return;
  }
  
  const [type, componentName, outputDir = '.'] = args;
  
  if (!componentName) {
    console.error('❌ 请提供组件名称');
    process.exit(1);
  }
  
  console.log(`\n📝 生成 ${type} 类型的组件: ${componentName}\n`);
  
  if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
  }
  
  generateComponent(type, componentName, outputDir);
}

main();
