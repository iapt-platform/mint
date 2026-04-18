import { List } from "antd";

import DiscussionItem from "./DiscussionItem";
import type { IComment } from "../../api/discussion";

interface IWidget {
  data: IComment[];
  onSelect?: (
    e: React.MouseEvent<HTMLSpanElement, MouseEvent>,
    data: IComment
  ) => void;
  onDelete?: (id: string) => void;
  onClose?: (item: IComment) => void;
}
const DiscussionListWidget = ({
  data,
  onSelect,
  onDelete,
  onClose,
}: IWidget) => {
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
              if (item.id) {
                onDelete?.(item.id);
              }
            }}
            onClose={() => {
              if (typeof onClose !== "undefined") {
                onClose(item);
              }
            }}
          />
        </List.Item>
      )}
    />
  );
};

export default DiscussionListWidget;
