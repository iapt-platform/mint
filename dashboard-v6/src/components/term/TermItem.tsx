import { Button, Card, Dropdown, Space, Typography } from "antd";
import {
  MoreOutlined,
  EditOutlined,
  TranslationOutlined,
} from "@ant-design/icons";

import type { ITerm, Term } from "../../api/Term";

import TimeShow from "../general/TimeShow";
import TermModal from "./TermModal";
import { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router";
import { useAppSelector } from "../../hooks";
import { click, clickedTerm } from "../../reducers/term-click";
import store from "../../store";
import "../article/article.css";
import Discussion from "../discussion/Discussion";
import { useIntl } from "react-intl";
import User from "../auth/User";
import MarkupView from "../markup/MarkupView";
import type { MarkupNode } from "../markup/types";
import { articlePath } from "../../utils";

const { Text } = Typography;

interface IWidget {
  data?: Term;
  onTermClick?: (clicked: ITerm) => void;
}
const TermItemWidget = ({ data, onTermClick }: IWidget) => {
  const [openTermModal, setOpenTermModal] = useState(false);
  const [showDiscussion, setShowDiscussion] = useState(false);
  const navigate = useNavigate();
  const termClicked = useAppSelector(clickedTerm);
  const intl = useIntl();

  useEffect(() => {
    console.debug("on redux", termClicked, data);
    if (!termClicked) {
      return;
    }
    if (termClicked?.channelId === (data?.channel_id ?? undefined)) {
      store.dispatch(click(null));
      onTermClick?.(termClicked);
    }
  }, [data, termClicked]);

  return (
    <>
      <Card
        bodyStyle={{ padding: 8 }}
        title={
          <Space orientation="vertical" size={3}>
            <Space>
              <Link to={`/term/show/${data?.id}`}>
                <Text strong>{data?.meaning}</Text>
              </Link>
              <Text type="secondary">{data?.other_meaning}</Text>
            </Space>
            <Space style={{ fontSize: "80%" }}>
              {data?.channel
                ? data.channel.name
                : intl.formatMessage({
                    id: "term.general-in-studio",
                  })}
              <Text type="secondary">
                <User {...(data?.editor ?? {})} showAvatar={false} />
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
                    navigate(articlePath("term", data?.id ?? ""));
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
          <MarkupView nodes={data?.body as MarkupNode[] | undefined} />
        </div>
        {showDiscussion ? (
          <Discussion resType="term" resId={data?.id} />
        ) : (
          <div style={{ textAlign: "right" }}>
            <Button type="link" onClick={() => setShowDiscussion(true)}>
              纠错
            </Button>
          </div>
        )}
      </Card>
      <TermModal
        id={data?.id}
        open={openTermModal}
        onClose={() => setOpenTermModal(false)}
      />
    </>
  );
};

export default TermItemWidget;
