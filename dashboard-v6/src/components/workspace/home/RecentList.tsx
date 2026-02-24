import type { CSSProperties } from "react";
import type { RecentItem as RecentItemType } from "../../../api/workspace";
import RecentItem from "./RecentItem";

type RecentListProps = {
  items: RecentItemType[];
};

export default function RecentList({ items }: RecentListProps) {
  return (
    <div style={styles.list}>
      {items.map((item) => (
        <RecentItem key={item.id} {...item} />
      ))}
    </div>
  );
}

const styles: Record<string, CSSProperties> = {
  list: {
    background: "#fff",
    borderRadius: 12,
    border: "1px solid #ede9e3",
    overflow: "hidden",
  },
};
