import { useNavigate } from "react-router-dom";
import { Button, Card, Divider } from "antd";
import { useParams } from "react-router-dom";
import { ArrowLeftOutlined } from "@ant-design/icons";

import CommentAnchor from "../../../components/discussion/DiscussionAnchor";
import { IComment } from "../../../components/discussion/DiscussionItem";
import CommentTopic from "../../../components/discussion/DiscussionTopic";
import { useState } from "react";

const Widget = () => {
  const { id } = useParams(); //url 参数
  const navigate = useNavigate();
  const [discussion, setDiscussion] = useState<IComment>();
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
        <CommentTopic
          topicId={id}
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
