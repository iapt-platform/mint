import { Divider } from "antd";
import { useNavigate, useParams } from "react-router-dom";
import DiscussionAnchor from "../../../components/discussion/DiscussionAnchor";
import { IComment } from "../../../components/discussion/DiscussionItem";
import DiscussionListCard, {
  TResType,
} from "../../../components/discussion/DiscussionListCard";

const Widget = () => {
  const { type, id } = useParams(); //url 参数
  const navigate = useNavigate();

  return (
    <>
      <DiscussionAnchor resId={id} resType={type as TResType} />
      <Divider></Divider>
      <DiscussionListCard
        resId={id}
        resType={type as TResType}
        onSelect={(
          e: React.MouseEvent<HTMLSpanElement, MouseEvent>,
          comment: IComment
        ) => {
          if (comment.id) {
            navigate(`/discussion/topic/${comment.id}`);
          } else {
            navigate(
              `/discussion/topic/${
                comment.tplId
              }?tpl=true&resId=${id}&resType=${type as TResType}`
            );
          }
        }}
        onReply={(comment: IComment) =>
          navigate(`/discussion/topic/${comment.id}`)
        }
      />
    </>
  );
};

export default Widget;
