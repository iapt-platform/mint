import { Button, Card, Dropdown, Space, Typography } from "antd";
import {
  MoreOutlined,
  EditOutlined,
  TranslationOutlined,
} from "@ant-design/icons";

import { ITermDataResponse } from "../api/Term";
import MdView from "../template/MdView";
import UserName from "../auth/UserName";
import TimeShow from "../general/TimeShow";
import TermModal from "./TermModal";
import { useEffect, useState } from "react";
import StudioName from "../auth/StudioName";
import { Link, useNavigate } from "react-router-dom";
import { useAppSelector } from "../../hooks";
import { clickedTerm } from "../../reducers/term-click";

const { Text } = Typography;

interface IWidget {
  data?: ITermDataResponse;
  onTermClick?: Function;
}
const TermItemWidget = ({ data, onTermClick }: IWidget) => {
  const [openTermModal, setOpenTermModal] = useState(false);
  const navigate = useNavigate();
  const termClicked = useAppSelector(clickedTerm);

  useEffect(() => {
    console.debug("on redux", termClicked, data);
    if (termClicked?.channelId === data?.channel?.id) {
      if (typeof onTermClick !== "undefined") {
        onTermClick(termClicked);
      }
    }
  }, [data?.channel?.id, termClicked]);

  return (
    <>
      <Card
        title={
          <Space direction="vertical" size={3}>
            <Space>
              <Link to={`/term/show/${data?.guid}`}>
                <Text strong>{data?.meaning}</Text>
              </Link>
              <Text type="secondary">{data?.other_meaning}</Text>
            </Space>
            <Space style={{ fontSize: "80%" }}>
              <StudioName data={data?.studio} />
              {data?.channel ? data.channel.name : "通用于此studio"}
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
                  icon: <TranslationOutlined />,
                },
              ],
              onClick: (e) => {
                console.log("click ", e);
                switch (e.key) {
                  case "edit":
                    setOpenTermModal(true);
                    break;
                  case "translate":
                    navigate(`/article/term/${data?.guid}`);
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
