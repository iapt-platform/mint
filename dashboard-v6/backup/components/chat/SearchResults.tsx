import React, { _useState } from "react";
import { List, _Pagination, Typography, _Card, _Space } from "antd";

import type { ElasticsearchResponse, WikipaliDocument } from "../../types/search"

const { Paragraph } = Typography;

interface SearchResultsProps {
  data: ElasticsearchResponse<WikipaliDocument>;
  onPageChange?: (page: number, pageSize: number) => void;
  pageSize?: number;
}

const SearchResults: React.FC<SearchResultsProps> = ({
  data,
  onPageChange,
  pageSize = 20,
}) => {
  return (
    <div className="search-results">
      {/* 搜索结果列表 */}
      <List
        size="small"
        dataSource={data.hits.hits}
        pagination={{
          onChange: onPageChange,
          onShowSizeChange: onPageChange,
          pageSize: pageSize,
          total: data.hits.total.value,
          showQuickJumper: true,
          showTotal: (total, range) =>
            `第 ${range[0]}-${range[1]} 条，共 ${total} 条`,
        }}
        renderItem={(item, _index) => {
          const previewText = item._source.content.text;
          return (
            <List.Item key={item._id}>
              <List.Item.Meta
                title={item._source.title.text}
                description={
                  <>
                    <Paragraph
                      type="secondary"
                      ellipsis={{ rows: 2, expandable: true }}
                    >
                      {previewText}
                    </Paragraph>
                    <div>{item._source.path}</div>
                  </>
                }
              />
            </List.Item>
          );
        }}
      />
    </div>
  );
};

export default SearchResults;
