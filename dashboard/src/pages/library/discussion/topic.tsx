import { useNavigate } from "react-router-dom";
import { Button, Card, Divider } from "antd";
import { useParams } from "react-router-dom";
import { ArrowLeftOutlined } from "@ant-design/icons";

import CommentAnchor from "../../../components/discussion/DiscussionAnchor";
import { IComment } from "../../../components/discussion/DiscussionItem";
import DiscussionTopic from "../../../components/discussion/DiscussionTopic";
import { useEffect, useLayoutEffect, useState } from "react";

const Widget = () => {
  const { id } = useParams(); //url 参数
  const navigate = useNavigate();
  const [discussion, setDiscussion] = useState<IComment>();
  const href = window.location.href.split("#");
  const anchor = href.length > 1 ? href[1] : undefined;

  useEffect(() => {
    const ele = document.getElementById(`answer-${anchor}`);
    ele?.scrollIntoView();
  });
  return (
    <>
      <CommentAnchor resId={discussion?.resId} resType={discussion?.resType} />
      <Divider></Divider>
      <Card
        title={
          <Button
            type="link"
            disabled={discussion ? false : true}
            icon={<ArrowLeftOutlined />}
            onClick={() =>
              navigate(
                `/discussion/show/${discussion?.resType}/${discussion?.resId}`
              )
            }
          >
            {"全部"}
          </Button>
        }
      >
        <DiscussionTopic
          topicId={id}
          focus={anchor}
          onTopicReady={(value: IComment) => {
            console.log("onTopicReady");
            setDiscussion(value);
          }}
        />
      </Card>
    </>
  );
};

export default Widget;
