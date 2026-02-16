import { List } from "antd";

const maxRecent = 10;
const storeKey = "grammar-handbook/recent";
export interface IGrammarRecent {
  title: string;
  description?: string;
  word?: string;
  wordId?: string;
}

export const popRecent = (): IGrammarRecent | null => {
  const old = localStorage.getItem(storeKey);
  if (old) {
    const recentList = JSON.parse(old);
    const top = recentList.shift();
    localStorage.setItem(storeKey, JSON.stringify(recentList));
    return top;
  } else {
    return null;
  }
};

export const pushRecent = (value: IGrammarRecent) => {
  const old = localStorage.getItem(storeKey);
  if (old) {
    const newRecent = [value, ...JSON.parse(old)].slice(0, maxRecent - 1);
    localStorage.setItem(storeKey, JSON.stringify(newRecent));
  } else {
    localStorage.setItem(storeKey, JSON.stringify([value]));
  }
};

interface IWidget {
  onClick?: Function;
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
            if (typeof onClick !== "undefined") {
              onClick(item);
            }
          }}
        >
          <List.Item.Meta title={item.title} description={item.description} />
        </List.Item>
      )}
    />
  );
};

export default GrammarRecentWidget;
