import React, { useEffect, useImperativeHandle, useRef, useState } from 'react';
import { Table, Input, Space, Button } from 'antd';
import { SearchOutlined } from '@ant-design/icons';
import type { TableProps, TablePaginationConfig } from 'antd/es/table'; // eslint-disable-line
import type { SorterResult, FilterValue, ColumnType } from 'antd/es/table/interface';

// 类型定义
export interface ActionType {
  reload: (resetPageIndex?: boolean) => void;
  reset: () => void;
  clearSelected?: () => void;
}

export interface ProColumns<T = any> extends Omit<ColumnType<T>, 'render' | 'filters' | 'onFilter'> {
  title?: React.ReactNode;
  dataIndex?: string | string[];
  key?: string;
  width?: number | string;
  search?: boolean | SearchConfig;
  hideInTable?: boolean;
  tooltip?: string;
  ellipsis?: boolean;
  valueType?: 'text' | 'date' | 'dateTime' | 'option' | 'money' | 'index';
  valueEnum?: Record<string, { text: React.ReactNode; status?: string }>;
  render?: (
    dom: any,
    entity: T,
    index: number,
    action: ActionType,
    schema?: ProColumns<T>
  ) => React.ReactNode;
  filters?: boolean;
  onFilter?: boolean | ((value: any, record: T) => boolean);
  sorter?: boolean | ((a: T, b: T) => number);
}

interface SearchConfig {
  transform?: (value: any) => any;
}

export interface RequestData<T> {
  data: T[];
  success?: boolean;
  total?: number;
}

export interface ProTableProps<T = any> {
  columns: ProColumns<T>[];
  request?: (
    params: Record<string, any>,
    sorter: Record<string, any>,
    filter: Record<string, any>
  ) => Promise<RequestData<T>>;
  actionRef?: React.MutableRefObject<ActionType | undefined>;
  rowKey?: string | ((record: T) => string);
  bordered?: boolean;
  pagination?: false | TablePaginationConfig;
  search?: false | { labelWidth?: number | 'auto' };
  options?: {
    search?: boolean;
    reload?: boolean;
    density?: boolean;
    setting?: boolean;
  };
  toolBarRender?: () => React.ReactNode[];
  toolbar?: {
    menu?: {
      activeKey?: React.Key;
      items?: Array<{
        key: string;
        label: React.ReactNode;
      }>;
      onChange?: (key: React.Key) => void;
    };
  };
  headerTitle?: React.ReactNode;
  params?: Record<string, any>;
}

const ProTable = <T extends Record<string, any>>({
  columns,
  request,
  actionRef,
  rowKey = 'id',
  bordered = false,
  pagination = {},
  search = false,
  options = {},
  toolBarRender,
  toolbar,
  headerTitle,
  params: externalParams,
  ...restProps
}: ProTableProps<T>) => {
  const [loading, setLoading] = useState(false);
  const [dataSource, setDataSource] = useState<T[]>([]);
  const [total, setTotal] = useState(0);
  const [currentPage, setCurrentPage] = useState(1);
  const [pageSize, setPageSize] = useState(
    typeof pagination === 'object' ? pagination.defaultPageSize || 20 : 20
  );
  const [searchKeyword, setSearchKeyword] = useState('');
  const [sorter, setSorter] = useState<Record<string, any>>({});
  const [filters, setFilters] = useState<Record<string, any>>({});

  // 创建内部 ref
  const internalActionRef = useRef<ActionType>({
    reload: async (resetPageIndex = false) => {
      if (resetPageIndex) {
        setCurrentPage(1);
      }
      await fetchData(resetPageIndex ? 1 : currentPage);
    },
    reset: () => {
      setSearchKeyword('');
      setCurrentPage(1);
      setSorter({});
      setFilters({});
    },
  });

  // 暴露 actionRef
  useImperativeHandle(actionRef, () => internalActionRef.current);

  const fetchData = async (page = currentPage) => {
    if (!request) return;

    setLoading(true);
    try {
      const params = {
        current: page,
        pageSize,
        keyword: searchKeyword,
        ...externalParams,
      };

      const result = await request(params, sorter, filters);
      
      setDataSource(result.data || []);
      setTotal(result.total || 0);
    } catch (error) {
      console.error('ProTable fetch error:', error);
    } finally {
      setLoading(false);
    }
  };

  // 监听参数变化
  useEffect(() => {
    fetchData(1);
    setCurrentPage(1);
  }, [searchKeyword, sorter, filters, JSON.stringify(externalParams)]);

  // 处理表格变化
  const handleTableChange = (
    newPagination: TablePaginationConfig,
    newFilters: Record<string, FilterValue | null>,
    newSorter: SorterResult<T> | SorterResult<T>[]
  ) => {
    // 处理分页
    if (newPagination.current !== currentPage) {
      setCurrentPage(newPagination.current || 1);
      fetchData(newPagination.current || 1);
    }
    if (newPagination.pageSize !== pageSize) {
      setPageSize(newPagination.pageSize || 20);
      setCurrentPage(1);
    }

    // 处理排序
    const sorterResult = Array.isArray(newSorter) ? newSorter[0] : newSorter;
    if (sorterResult && sorterResult.field) {
      setSorter({
        [sorterResult.field as string]: sorterResult.order,
      });
    } else {
      setSorter({});
    }

    // 处理过滤
    const validFilters: Record<string, any> = {};
    Object.entries(newFilters).forEach(([key, value]) => {
      if (value && value.length > 0) {
        validFilters[key] = value;
      }
    });
    setFilters(validFilters);
  };

  // 转换列配置
  const processedColumns = columns
    .filter((col) => !col.hideInTable)
    .map((col) => {
      const processed: any = { ...col };

      // 处理 valueEnum 为 filters
      if (col.valueEnum && col.filters) {
        processed.filters = Object.entries(col.valueEnum).map(([key, value]) => ({
          text: value.text,
          value: key,
        }));
        if (col.onFilter) {
          processed.onFilter = (value: any, record: T) => {
            const dataValue = col.dataIndex
              ? record[col.dataIndex as string]
              : undefined;
            return dataValue === value;
          };
        }
      }

      // 处理 valueType
      if (col.valueType === 'date' || col.valueType === 'dateTime') {
        const originalRender = processed.render;
        processed.render = (text: any, record: T, index: number) => {
          if (originalRender) {
            return originalRender(text, record, index, internalActionRef.current, col);
          }
          if (!text) return '-';
          const date = new Date(text);
          if (col.valueType === 'date') {
            return date.toLocaleDateString();
          }
          return date.toLocaleString();
        };
      }

      // 处理自定义 render
      if (col.render && processed.render !== col.render) {
        const customRender = col.render;
        processed.render = (text: any, record: T, index: number) => {
          return customRender(text, record, index, internalActionRef.current, col);
        };
      }

      // 处理 ellipsis 和 tooltip
      if (col.ellipsis) {
        processed.ellipsis = {
          showTitle: col.tooltip !== undefined,
        };
      }

      return processed;
    });

  // 构建工具栏
  const renderToolbar = () => {
    const menuItems = toolbar?.menu?.items || [];
    const activeKey = toolbar?.menu?.activeKey;
    const onChange = toolbar?.menu?.onChange;

    return (
      <div
        style={{
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
          marginBottom: 16,
          padding: '16px 0',
        }}
      >
        <div style={{ display: 'flex', gap: 16, alignItems: 'center' }}>
          {headerTitle}
          {menuItems.length > 0 && (
            <Space>
              {menuItems.map((item) => (
                <Button
                  key={item.key}
                  type={activeKey === item.key ? 'primary' : 'default'}
                  onClick={() => onChange?.(item.key)}
                >
                  {item.label}
                </Button>
              ))}
            </Space>
          )}
        </div>
        <Space>{toolBarRender?.()}</Space>
      </div>
    );
  };

  // 构建搜索栏
  const renderSearch = () => {
    if (!options.search) return null;

    return (
      <div style={{ marginBottom: 16 }}>
        <Input.Search
          placeholder="请输入关键词搜索"
          allowClear
          enterButton={<SearchOutlined />}
          value={searchKeyword}
          onChange={(e) => setSearchKeyword(e.target.value)}
          onSearch={(value) => {
            setSearchKeyword(value);
            setCurrentPage(1);
          }}
          style={{ maxWidth: 400 }}
        />
      </div>
    );
  };

  const paginationConfig: TablePaginationConfig | false =
    pagination === false
      ? false
      : {
          current: currentPage,
          pageSize,
          total,
          showSizeChanger: true,
          showQuickJumper: true,
          showTotal: (total) => `共 ${total} 条`,
          onChange: (page, newPageSize) => {
            setCurrentPage(page);
            if (newPageSize !== pageSize) {
              setPageSize(newPageSize);
              setCurrentPage(1);
            }
          },
          ...pagination,
        };

  return (
    <div className="pro-table">
      {renderToolbar()}
      {renderSearch()}
      <Table<T>
        {...restProps}
        columns={processedColumns}
        dataSource={dataSource}
        loading={loading}
        rowKey={rowKey}
        bordered={bordered}
        pagination={paginationConfig}
        onChange={handleTableChange}
      />
    </div>
  );
};

export default ProTable;
