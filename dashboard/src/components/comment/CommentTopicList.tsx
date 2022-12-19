import { Card, List, Avatar, Space } from "antd";
import { MessageOutlined } from "@ant-design/icons";

import CommentItem, { IComment } from "./CommentItem";
import { Link } from "react-router-dom";

interface IWidget {
  data: IComment[];
}
const Widget = ({ data }: IWidget) => {
  return (
    <div>
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
            <CommentItem data={item} />
          </List.Item>
        )}
      />
    </div>
  );
};

export default Widget;
