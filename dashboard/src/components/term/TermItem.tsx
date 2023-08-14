import { Button, Card, Dropdown, Space, Typography } from "antd";
import { MoreOutlined, EditOutlined } from "@ant-design/icons";

import { ITermDataResponse } from "../api/Term";
import MdView from "../template/MdView";
import UserName from "../auth/UserName";
import TimeShow from "../general/TimeShow";
import TermModal from "./TermModal";
import { useState } from "react";

const { Text } = Typography;

interface IWidget {
  data?: ITermDataResponse;
}
const TermItemWidget = ({ data }: IWidget) => {
  const [openTermModal, setOpenTermModal] = useState(false);
  return (
    <>
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
              <TimeShow type="secondary" updatedAt={data?.updated_at} />
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
                    setOpenTermModal(true);
                    break;

                  default:
                    break;
                }
              },
            }}
            placement="bottomRight"
          >
            <Button
              size="small"
              shape="circle"
              icon={<MoreOutlined />}
            ></Button>
          </Dropdown>
        }
      >
        <MdView html={data?.html} />
      </Card>
      <TermModal
        id={data?.guid}
        open={openTermModal}
        onClose={() => setOpenTermModal(false)}
      />
    </>
  );
};

export default TermItemWidget;
