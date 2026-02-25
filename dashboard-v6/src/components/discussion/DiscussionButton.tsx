import { Space, Tooltip } from "antd";

import { count } from "../../reducers/discussion";
import { CommentFillIcon, CommentOutlinedIcon } from "../../assets/icon";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";
import { discussionList } from "../../reducers/discussion-count";
import type {
  IDiscussionCountData,
  IDiscussionCountWbw,
} from "../../api/Comment";
import { useMemo } from "react";
import type { TResType } from "../../api/discussion";
import { openDiscussion } from "./utils";

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
  const discussionCount = useAppSelector(count);

  const CommentCount = useMemo(() => {
    if (
      discussionCount?.resType === "sentence" &&
      discussionCount.resId === resId
    ) {
      return discussionCount.count;
    } else {
      return initCount;
    }
  }, [discussionCount, resId, initCount]);

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

  //console.debug("DiscussionButton", discussions, wbw, withStudent);

  let currCount = CommentCount;
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
        onClick={() => {
          if (resId) {
            openDiscussion(resId, resType, wbw ? true : false);
          }
        }}
      >
        {myCount ? <CommentFillIcon /> : <CommentOutlinedIcon />}
        {hideCount ? <></> : currCount}
      </Space>
    </Tooltip>
  );
};

export default DiscussionButton;
