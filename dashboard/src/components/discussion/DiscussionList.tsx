import { List } from "antd";

import DiscussionItem, { IComment } from "./DiscussionItem";

interface IWidget {
  data: IComment[];
  onSelect?: Function;
  onDelete?: Function;
}
const DiscussionListWidget = ({ data, onSelect, onDelete }: IWidget) => {
  return (
    <List
      pagination={{
        onChange: (page) => {
          console.log(page);
        },
        pageSize: 10,
      }}
      itemLayout="horizontal"
      dataSource={data}
      renderItem={(item) => (
        <List.Item>
          <DiscussionItem
            data={item}
            onSelect={(
              e: React.MouseEvent<HTMLSpanElement, MouseEvent>,
              data: IComment
            ) => {
              if (typeof onSelect !== "undefined") {
                onSelect(e, data);
              }
            }}
            onDelete={() => {
              if (typeof onDelete !== "undefined") {
                onDelete(item.id);
              }
            }}
          />
        </List.Item>
      )}
    />
  );
};

export default DiscussionListWidget;
