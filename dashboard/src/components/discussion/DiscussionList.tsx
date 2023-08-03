import { List } from "antd";

import DiscussionItem, { IComment } from "./DiscussionItem";

interface IWidget {
  data: IComment[];
  onSelect?: Function;
}
const DiscussionListWidget = ({ data, onSelect }: IWidget) => {
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
          />
        </List.Item>
      )}
    />
  );
};

export default DiscussionListWidget;
