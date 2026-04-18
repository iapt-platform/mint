import { List } from "antd";
import { storeKey } from "./utils";

export interface IGrammarRecent {
  title: string;
  description?: string;
  word?: string;
  wordId?: string;
}

interface IWidget {
  onClick?: (item: IGrammarRecent) => void;
}
const GrammarRecentWidget = ({ onClick }: IWidget) => {
  const data = localStorage.getItem(storeKey);
  let items: IGrammarRecent[] = [];
  if (data) {
    items = JSON.parse(data);
  }
  return (
    <List
      header={"最近搜索"}
      size="small"
      dataSource={items}
      renderItem={(item, index) => (
        <List.Item
          key={index}
          style={{ cursor: "pointer" }}
          onClick={() => {
            onClick?.(item);
          }}
        >
          <List.Item.Meta title={item.title} description={item.description} />
        </List.Item>
      )}
    />
  );
};

export default GrammarRecentWidget;
