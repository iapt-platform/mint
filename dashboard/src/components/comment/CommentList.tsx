import { Card, List, Avatar, Space } from "antd";
import { MessageOutlined } from "@ant-design/icons";

import { IComment } from "./CommentItem";
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
          <List.Item
            actions={[
              <Space>
                <MessageOutlined /> {"5"}
              </Space>,
            ]}
          >
            <List.Item.Meta
              avatar={<Avatar src="https://joeschmoe.io/api/v1/random" />}
              title={
                <Link to={`/comment/${item.id}`}>
                  {item.title ? item.title : item.content?.slice(0, 20)}
                </Link>
              }
              description={item.content?.slice(0, 40)}
            />
          </List.Item>
        )}
      />
    </div>
  );
};

export default Widget;
