// src/components/Recent.tsx
import { List, Alert } from "antd";
import { ClockCircleOutlined } from "@ant-design/icons";
import type { IGetRecentByUserParams } from "../../api/recent";
import { type IRecentData } from "../../api/recent";
import { useRecent } from "../../hooks/useRecent.ts";

interface RecentProps extends IGetRecentByUserParams {
  onClick?: (id: string) => void;
}

export const Recent = ({
  userId,
  pageSize = 20,
  page = 0,
  type,
  onClick,
}: RecentProps) => {
  const { data, loading, errorCode, refresh } = useRecent(
    userId,
    pageSize,
    page,
    type
  );

  if (errorCode !== null) {
    return (
      <Alert
        type="error"
        title={`加载失败（错误码：${errorCode}）`}
        action={
          <a onClick={refresh} style={{ cursor: "pointer" }}>
            重试
          </a>
        }
      />
    );
  }

  const rows: IRecentData[] = data?.data?.rows ?? [];

  return (
    <List<IRecentData>
      loading={loading}
      dataSource={rows}
      locale={{ emptyText: "暂无最近访问记录" }}
      renderItem={(item) => (
        <List.Item
          key={item.id}
          onClick={() => onClick?.(item.id)}
          style={{ cursor: onClick ? "pointer" : "default" }}
        >
          <List.Item.Meta
            avatar={
              <ClockCircleOutlined style={{ fontSize: 16, marginTop: 4 }} />
            }
            title={item.title}
            description={
              <span style={{ fontSize: 12, color: "#999" }}>
                {item.type}
                {item.updated_at
                  ? ` · ${new Date(item.updated_at).toLocaleString("zh-CN")}`
                  : ""}
              </span>
            }
          />
        </List.Item>
      )}
    />
  );
};
