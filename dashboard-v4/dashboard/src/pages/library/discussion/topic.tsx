import { useEffect, useState } from "react";
import { Link, useNavigate, useSearchParams } from "react-router-dom";
import { Button, Card, Divider, message } from "antd";
import { useParams } from "react-router-dom";
import { ArrowLeftOutlined } from "@ant-design/icons";

import CommentAnchor, {
  IAnchor,
} from "../../../components/discussion/DiscussionAnchor";
import { IComment } from "../../../components/discussion/DiscussionItem";
import DiscussionTopic from "../../../components/discussion/DiscussionTopic";
import { TResType } from "../../../components/discussion/DiscussionListCard";
import { get } from "../../../request";
import { IArticleResponse } from "../../../components/api/Article";

const Widget = () => {
  const { id } = useParams(); //url 参数
  const navigate = useNavigate();
  const [searchParams, setSearchParams] = useSearchParams();
  const [discussion, setDiscussion] = useState<IComment>();
  const [topic, setTopic] = useState<IComment>();
  const [anchorInfo, setAnchorInfo] = useState<IAnchor>();
  const isTpl = searchParams.get("tpl");
  const resId = searchParams.get("resId");
  const resType = searchParams.get("resType");

  useEffect(() => {
    if (isTpl) {
      const url = `/v2/article/${id}`;
      console.log("url", url);
      get<IArticleResponse>(url).then((json) => {
        console.log("json", json);
        if (!json.ok) {
          message.error(json.message);
          return;
        }
        const value = json.data;
        if (typeof value.editor === "undefined" || resId === null) {
          console.error("no editor or resId");
          return;
        }
        const topicInfo: IComment = {
          resId: resId,
          resType: resType as TResType,
          type: "discussion",
          user: value.editor,
          title: value.title,
          tplId: id,
          content: value.content,
          createdAt: value.created_at,
          updatedAt: value.updated_at,
        };
        console.log("topicInfo", topicInfo);
        setTopic(topicInfo);
      });
    }
  }, [id, isTpl, resId, resType]);

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
        resId={resId ? resId : discussion?.resId}
        resType={resType ? (resType as TResType) : discussion?.resType}
        onLoad={(value: IAnchor) => {
          setAnchorInfo(value);
        }}
      />
      <Divider></Divider>
      <Card
        title={
          <Button
            type="link"
            disabled={resId ? false : discussion ? false : true}
            icon={<ArrowLeftOutlined />}
            onClick={() =>
              navigate(
                `/discussion/show/${resType ? resType : discussion?.resType}/${
                  resId ? resId : discussion?.resId
                }`
              )
            }
          >
            {"全部"}
          </Button>
        }
      >
        <DiscussionTopic
          resType={resType ? (resType as TResType) : undefined}
          topicId={isTpl ? undefined : id}
          topic={topic}
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
