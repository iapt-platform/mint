import { Space, Tooltip } from "antd";
import store from "../../store";
import { IShowDiscussion, show } from "../../reducers/discussion";
import { openPanel } from "../../reducers/right-panel";
import { CommentFillIcon, CommentOutlinedIcon } from "../../assets/icon";
import { TResType } from "./DiscussionListCard";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";
import { discussionList } from "../../reducers/discussion-count";
import { IDiscussionCountData, IDiscussionCountWbw } from "../api/Comment";

interface IWidget {
  initCount?: number;
  resId?: string;
  resType?: TResType;
  hideCount?: boolean;
  hideInZero?: boolean;
  onlyMe?: boolean;
  wbw?: IDiscussionCountWbw;
}
const DiscussionButton = ({
  initCount = 0,
  resId,
  resType = "sentence",
  hideCount = false,
  hideInZero = false,
  onlyMe = false,
  wbw,
}: IWidget) => {
  const user = useAppSelector(currentUser);
  const discussions = useAppSelector(discussionList);

  const all = discussions?.filter((value) => value.res_id === resId);
  const my = all?.filter((value) => value.editor_uid === user?.id);

  let withStudent: IDiscussionCountData[] | undefined;
  if (wbw) {
    withStudent = discussions?.filter(
      (value) =>
        value.wbw?.book_id === wbw?.book_id &&
        value.wbw?.paragraph === wbw?.paragraph &&
        value.wbw?.wid.toString() === wbw?.wid.toString()
    );
  }

  console.debug("DiscussionButton", discussions, wbw, withStudent);

  let currCount = initCount;
  if (onlyMe) {
    if (my) {
      currCount = my.length;
    } else {
      currCount = 0;
    }
  } else {
    if (all) {
      currCount = all.length;
    } else {
      currCount = 0;
    }
    if (withStudent) {
      currCount += withStudent.length;
    }
  }

  let myCount = false;
  if (my && my.length > 0) {
    myCount = true;
  }

  return hideInZero && currCount === 0 ? (
    <></>
  ) : (
    <Tooltip title="讨论">
      <Space
        size={"small"}
        style={{
          cursor: "pointer",
          color: currCount && currCount > 0 ? "#1890ff" : "unset",
        }}
        onClick={(event) => {
          const data: IShowDiscussion = {
            type: "discussion",
            resId: resId,
            resType: resType,
            withStudent: wbw ? true : false,
          };
          console.debug("discussion show", data);
          store.dispatch(show(data));
          store.dispatch(openPanel("discussion"));
        }}
      >
        {myCount ? <CommentFillIcon /> : <CommentOutlinedIcon />}
        {hideCount ? <></> : currCount}
      </Space>
    </Tooltip>
  );
};

export default DiscussionButton;
