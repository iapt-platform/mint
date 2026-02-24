// ProTable 类型定义文件
import type { TableProps, TablePaginationConfig } from 'antd/es/table';
import type { SorterResult, ColumnType } from 'antd/es/table/interface';

/**
 * ActionType - 表格操作接口
 */
export interface ActionType {
  /** 刷新表格 */
  reload: (resetPageIndex?: boolean) => void;
  /** 重置表格状态 */
  reset: () => void;
  /** 清空选中项 */
  clearSelected?: () => void;
}

/**
 * 搜索配置
 */
export interface SearchConfig {
  /** 转换搜索值 */
  transform?: (value: any) => any;
}

/**
 * 列配置
 */
export interface ProColumns<T = any> extends Omit<ColumnType<T>, 'render' | 'filters' | 'onFilter'> {
  /** 列标题 */
  title?: React.ReactNode;
  /** 数据索引 */
  dataIndex?: string | string[];
  /** 唯一 key */
  key?: string;
  /** 列宽 */
  width?: number | string;
  /** 是否可搜索 */
  search?: boolean | SearchConfig;
  /** 是否在表格中隐藏 */
  hideInTable?: boolean;
  /** 提示信息 */
  tooltip?: string;
  /** 是否自动缩略 */
  ellipsis?: boolean;
  /** 值类型 */
  valueType?: 'text' | 'date' | 'dateTime' | 'option' | 'money' | 'index';
  /** 枚举值 */
  valueEnum?: Record<
    string,
    {
      text: React.ReactNode;
      status?: string;
    }
  >;
  /** 自定义渲染 */
  render?: (
    dom: any,
    entity: T,
    index: number,
    action: ActionType,
    schema?: ProColumns<T>
  ) => React.ReactNode;
  /** 是否支持过滤 */
  filters?: boolean;
  /** 过滤函数 */
  onFilter?: boolean | ((value: any, record: T) => boolean);
  /** 排序 */
  sorter?: boolean | ((a: T, b: T) => number);
}

/**
 * 请求返回数据格式
 */
export interface RequestData<T> {
  /** 数据列表 */
  data: T[];
  /** 是否成功 */
  success?: boolean;
  /** 总数 */
  total?: number;
}

/**
 * 工具栏菜单项
 */
export interface ToolbarMenuItem {
  key: string;
  label: React.ReactNode;
}

/**
 * 工具栏配置
 */
export interface ToolbarConfig {
  menu?: {
    /** 当前激活的 key */
    activeKey?: React.Key;
    /** 菜单项 */
    items?: ToolbarMenuItem[];
    /** 切换回调 */
    onChange?: (key: React.Key) => void;
  };
}

/**
 * 选项配置
 */
export interface OptionsConfig {
  /** 是否显示搜索 */
  search?: boolean;
  /** 是否显示刷新 */
  reload?: boolean;
  /** 是否显示密度 */
  density?: boolean;
  /** 是否显示设置 */
  setting?: boolean;
}

/**
 * ProTable 组件属性
 */
export interface ProTableProps<T = any> {
  /** 列配置 */
  columns: ProColumns<T>[];
  /** 请求数据的函数 */
  request?: (
    params: Record<string, any>,
    sorter: Record<string, any>,
    filter: Record<string, any>
  ) => Promise<RequestData<T>>;
  /** 表格操作引用 */
  actionRef?: React.MutableRefObject<ActionType | undefined>;
  /** 行唯一键 */
  rowKey?: string | ((record: T) => string);
  /** 是否显示边框 */
  bordered?: boolean;
  /** 分页配置 */
  pagination?: false | TablePaginationConfig;
  /** 搜索配置 */
  search?: false | { labelWidth?: number | 'auto' };
  /** 选项配置 */
  options?: OptionsConfig;
  /** 工具栏渲染 */
  toolBarRender?: () => React.ReactNode[];
  /** 工具栏配置 */
  toolbar?: ToolbarConfig;
  /** 标题 */
  headerTitle?: React.ReactNode;
  /** 额外参数 */
  params?: Record<string, any>;
}
