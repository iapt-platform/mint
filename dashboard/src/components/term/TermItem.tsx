import { Button, Card, Dropdown, Space, Typography } from "antd";
import { MoreOutlined, EditOutlined } from "@ant-design/icons";

import { ITermDataResponse } from "../api/Term";
import MdView from "../template/MdView";
import UserName from "../auth/UserName";
import TimeShow from "../general/TimeShow";

const { Text } = Typography;

interface IWidget {
  data?: ITermDataResponse;
}
const TermItemWidget = ({ data }: IWidget) => {
  return (
    <Card
      title={
        <Space direction="vertical" size={3}>
          <Space>
            <Text strong>{data?.meaning}</Text>
            <Text type="secondary">{data?.other_meaning}</Text>
          </Space>
          <Space style={{ fontSize: "80%" }}>
            <Text type="secondary">
              <UserName {...data?.editor} />
            </Text>
            <Text type="secondary">update at</Text>
            <TimeShow time={data?.updated_at} />
          </Space>
        </Space>
      }
      extra={
        <Dropdown
          key={1}
          trigger={["click"]}
          menu={{
            items: [
              {
                key: "edit",
                label: "edit",
                icon: <EditOutlined />,
              },
              {
                key: "translate",
                label: "translate",
                icon: <EditOutlined />,
              },
            ],
            onClick: (e) => {
              console.log("click ", e);
              switch (e.key) {
                case "edit":
                  break;

                default:
                  break;
              }
            },
          }}
          placement="bottomRight"
        >
          <Button size="small" shape="circle" icon={<MoreOutlined />}></Button>
        </Dropdown>
      }
    >
      <MdView html={data?.html} />
    </Card>
  );
};

export default TermItemWidget;
