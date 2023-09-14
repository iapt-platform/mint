import { useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { Button, Card, Divider } from "antd";
import { useParams } from "react-router-dom";
import { ArrowLeftOutlined } from "@ant-design/icons";

import CommentAnchor, {
  IAnchor,
} from "../../../components/discussion/DiscussionAnchor";
import { IComment } from "../../../components/discussion/DiscussionItem";
import DiscussionTopic from "../../../components/discussion/DiscussionTopic";

const Widget = () => {
  const { id } = useParams(); //url 参数
  const navigate = useNavigate();
  const [discussion, setDiscussion] = useState<IComment>();
  const [anchorInfo, setAnchorInfo] = useState<IAnchor>();
  const href = window.location.href.split("#");
  const anchor = href.length > 1 ? href[1] : undefined;

  const book = anchorInfo?.sentence?.book;
  const para = anchorInfo?.sentence?.paragraph;
  let openPara: number[] = [];
  if (para) {
    openPara = [para - 1, para, para + 1];
  }
  const linkId = `${book}-${para}`;
  const linkChannel = anchorInfo?.sentence?.channel.id;
  let linkOpen = `/article/para/${linkId}?mode=edit&book=${book}&par=${openPara.join(
    ","
  )}&channel=${linkChannel}&topic=${id}&dis_type=sentence`;
  if (anchor) {
    linkOpen += `&comment=${anchor}`;
  }
  return (
    <>
      {anchorInfo ? <Link to={linkOpen}>在翻译界面中打开</Link> : undefined}

      <CommentAnchor
        resId={discussion?.resId}
        resType={discussion?.resType}
        onLoad={(value: IAnchor) => {
          setAnchorInfo(value);
        }}
      />
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
