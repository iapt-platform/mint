import { Button, Card, Dropdown, Space, Typography } from "antd";
import {
  MoreOutlined,
  EditOutlined,
  TranslationOutlined,
} from "@ant-design/icons";

import { ITermDataResponse } from "../api/Term";
import MdView from "../template/MdView";
import TimeShow from "../general/TimeShow";
import TermModal from "./TermModal";
import { useEffect, useState } from "react";
import StudioName from "../auth/Studio";
import { Link, useNavigate } from "react-router-dom";
import { useAppSelector } from "../../hooks";
import { click, clickedTerm } from "../../reducers/term-click";
import store from "../../store";
import "../article/article.css";
import Discussion from "../discussion/Discussion";
import { useIntl } from "react-intl";
import User from "../auth/User";

const { Text } = Typography;

interface IWidget {
  data?: ITermDataResponse;
  onTermClick?: Function;
}
const TermItemWidget = ({ data, onTermClick }: IWidget) => {
  const [openTermModal, setOpenTermModal] = useState(false);
  const [showDiscussion, setShowDiscussion] = useState(false);
  const navigate = useNavigate();
  const termClicked = useAppSelector(clickedTerm);
  const intl = useIntl();

  useEffect(() => {
    console.debug("on redux", termClicked, data);
    if (typeof termClicked === "undefined") {
      return;
    }
    if (termClicked?.channelId === data?.channel?.id) {
      store.dispatch(click(null));
      if (typeof onTermClick !== "undefined") {
        onTermClick(termClicked);
      }
    }
  }, [data, termClicked]);

  return (
    <>
      <Card
        bodyStyle={{ padding: 8 }}
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
              {data?.channel
                ? data.channel.name
                : intl.formatMessage({
                    id: "term.general-in-studio",
                  })}
              <Text type="secondary">
                <User {...data?.editor} showAvatar={false} />
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
        <div className="pcd_article">
          <MdView html={data?.html} />
        </div>
        {showDiscussion ? (
          <Discussion resType="term" resId={data?.guid} />
        ) : (
          <div style={{ textAlign: "right" }}>
            <Button type="link" onClick={() => setShowDiscussion(true)}>
              纠错
            </Button>
          </div>
        )}
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
