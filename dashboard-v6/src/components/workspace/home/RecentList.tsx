import type { CSSProperties } from "react";
import { theme } from "antd";
import type { RecentItem as RecentItemType } from "../../../api/workspace";
import RecentItem from "./RecentItem";
import type { ArticleType } from "../../../api/article";

type RecentListProps = {
  items: RecentItemType[];
  onClick?: (type: ArticleType, id: string) => void;
};

export default function RecentList({ items, onClick }: RecentListProps) {
  const { token } = theme.useToken();

  const styles: Record<string, CSSProperties> = {
    list: {
      background: token.colorBgContainer,
      borderRadius: token.borderRadiusLG,
      border: `1px solid ${token.colorBorderSecondary}`,
      overflow: "hidden",
    },
  };

  return (
    <div style={styles.list}>
      {items.map((item) => (
        <RecentItem
          key={item.id}
          {...item}
          onClick={() => {
            onClick?.(item.type, item.id);
          }}
        />
      ))}
    </div>
  );
}
