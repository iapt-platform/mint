import { Typography, Space, message } from "antd";
import { useEffect, useState } from "react";
import { get } from "../../request";
import { ICommentResponse } from "../api/Comment";
import TimeShow from "../general/TimeShow";

import { IComment } from "./DiscussionItem";

const { Title, Text } = Typography;

interface IWidget {
  topicId?: string;
  onReady?: Function;
}
const DiscussionTopicInfoWidget = ({ topicId, onReady }: IWidget) => {
  const [data, setData] = useState<IComment>();
  useEffect(() => {
    if (typeof topicId === "undefined") {
      return;
    }
    get<ICommentResponse>(`/v2/discussion/${topicId}`)
      .then((json) => {
        console.log(json);
        if (json.ok) {
          console.log("flashes.success");
          const item = json.data;
          const discussion: IComment = {
            id: item.id,
            resId: item.res_id,
            resType: item.res_type,
            parent: item.parent,
            user: item.editor,
            title: item.title,
            content: item.content,
            createdAt: item.created_at,
            updatedAt: item.updated_at,
          };
          setData(discussion);
          if (typeof onReady !== "undefined") {
            console.log("on ready");
            onReady(discussion);
          }
        } else {
          message.error(json.message);
        }
      })
      .catch((e) => {
        message.error(e.message);
      });
  }, [topicId]);
  return (
    <div>
      <Title editable level={5} style={{ margin: 0 }}>
        {data?.title}
      </Title>
      <div>
        <Text type="secondary">
          <Space>
            {data?.user.nickName}
            <TimeShow updatedAt={data?.updatedAt} createdAt={data?.createdAt} />
          </Space>
        </Text>
      </div>
      <div
        style={{ maxWidth: 800, overflow: "auto" }}
        dangerouslySetInnerHTML={{
          __html: data?.content ? data?.content : "",
        }}
      />
    </div>
  );
};

export default DiscussionTopicInfoWidget;
