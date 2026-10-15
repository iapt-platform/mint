import { useEffect, useState } from "react";
import { Link, useLocation, useNavigate, useParams } from "react-router";
import { Button, Card, Result, Space, Spin, Tag } from "antd";
import { ArrowLeftOutlined } from "@ant-design/icons";

import DiscussionTopic from "../../components/discussion/DiscussionTopic";
import DiscussionAnchor from "../../components/discussion/DiscussionAnchor";
import { fetchDiscussion, type IComment } from "../../api/discussion";

/**
 * 单个话题页：`/discussion/topic/:topicId`，回复用 `#<回复 id>` 定位。
 *
 * 「复制链接」拷出来的就是这个地址。放在 workspace 之外、不要求登录：
 * qa / help 对匿名公开，能不能看由后端的可见性规则决定（看不到时是 404）。
 */
const Widget = () => {
  const { topicId } = useParams();
  const { hash } = useLocation();
  const navigate = useNavigate();
  // 记下取的是哪条，路由换了话题时旧结果自然失效，不必在 effect 里先清状态
  const [result, setResult] = useState<{
    id: string;
    topic?: IComment;
    failed?: boolean;
  }>();

  useEffect(() => {
    if (!topicId) {
      return;
    }
    fetchDiscussion(topicId)
      .then((topic) => setResult({ id: topicId, topic }))
      .catch(() => setResult({ id: topicId, failed: true }));
  }, [topicId]);

  const current = result?.id === topicId ? result : undefined;
  const topic = current?.topic;

  const back = (
    <Button icon={<ArrowLeftOutlined />} onClick={() => navigate("/")}>
      首页
    </Button>
  );

  let body;
  if (current?.failed) {
    body = <Result status="404" title="话题不存在或无权查看" extra={back} />;
  } else if (!topic) {
    body = <Spin style={{ display: "block", margin: 48 }} />;
  } else {
    body = (
      <>
        {/* 讨论挂靠的内容：句子 / 文章 */}
        <DiscussionAnchor resId={topic.resId} resType={topic.resType} />
        <Card
          style={{ marginTop: 16 }}
          title={
            <Space>
              <Tag>{topic.type}</Tag>
              <Link to={`/discussion/show/${topic.resType}/${topic.resId}`}>
                这里的全部讨论
              </Link>
            </Space>
          }
        >
          <DiscussionTopic
            topicId={topic.id}
            topic={topic}
            resType={topic.resType}
            focus={hash ? hash.slice(1) : undefined}
            onTopicDelete={() => navigate("/")}
          />
        </Card>
      </>
    );
  }

  return (
    <div style={{ maxWidth: 960, margin: "0 auto", padding: 16 }}>
      <title>{topic?.title ?? "discussion"}</title>
      {body}
    </div>
  );
};

export default Widget;
