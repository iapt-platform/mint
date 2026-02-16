import { Button, List } from "antd";
import { DeleteOutlined } from "@ant-design/icons";

import User, { IUser } from "../auth/User";
import { useState } from "react";

interface IWidget {
  data?: IUser[];
  onDelete?: ((user: IUser) => Promise<boolean | void>) | undefined;
}
const WatchList = ({ data, onDelete }: IWidget) => {
  const [del, setDel] = useState<string>();
  return (
    <List
      dataSource={data}
      renderItem={(item) => (
        <List.Item
          extra={[
            <Button
              type="text"
              danger
              loading={item.id === del}
              icon={<DeleteOutlined />}
              onClick={() => {
                console.debug("delete", item);
                if (typeof onDelete !== "undefined") {
                  console.debug("delete", item);
                  setDel(item.id);
                  onDelete(item).finally(() => {
                    setDel(undefined);
                  });
                }
              }}
            />,
          ]}
        >
          <User {...item} />
        </List.Item>
      )}
    />
  );
};

export default WatchList;
