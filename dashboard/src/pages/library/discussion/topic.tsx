import { useNavigate } from "react-router-dom";
import { Tabs } from "antd";
import { useParams } from "react-router-dom";
import CommentAnchor from "../../../components/discussion/DiscussionAnchor";
import { IComment } from "../../../components/discussion/DiscussionItem";
import CommentListCard from "../../../components/discussion/DiscussionListCard";

import CommentTopic from "../../../components/discussion/DiscussionTopic";

const Widget = () => {
  // TODO
  const { id } = useParams(); //url 参数
  const navigate = useNavigate();
  return (
    <div>
      <div>
        <CommentAnchor id={id} />
      </div>
      <div>
        <Tabs
          defaultActiveKey="current"
          items={[
            {
              label: `当前`,
              key: "current",
              children: <CommentTopic topicId={id} />,
            },
            {
              label: `全部`,
              key: "all",
              children: (
                <CommentListCard
                  topicId={id}
                  onSelect={(
                    e: React.MouseEvent<HTMLSpanElement, MouseEvent>,
                    comment: IComment
                  ) => {
                    navigate(`/discussion/topic/${comment.id}`);
                  }}
                />
              ),
            },
            {
              label: `类似`,
              key: "sim",
              children: "",
            },
          ]}
        />
      </div>
    </div>
  );
};

export default Widget;
