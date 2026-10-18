import { useAppSelector } from "../../hooks";
import { courseUser } from "../../reducers/course-user";
import { currentUser } from "../../reducers/current-user";
import DiscussionButton from "../discussion/DiscussionButton";

import type { IStudio } from "../../api/Auth";
import type { IWbw } from "../../types/wbw";

interface IWidget {
  data: IWbw;
  studio?: IStudio;
  /** 老师视角按 channel 匹配学员讨论时要用，见下面的 TODO */
  channelId?: string;
}

/**
 * 逐词上的讨论图标（有讨论才显示）。
 *
 * 课程里的学员：看自己的逐词 channel 时显示全部讨论，看别人的只显示自己发起的。
 *
 * TODO: 课程迁 v3 之后补回老师视角的「学生的讨论」：老师在课程答案 channel 上，
 * 同时看到学员们在同一个词上的讨论（v4 传 wbw 坐标 {book_id, paragraph, wid}，由 v2
 * discussion-count 的 course_id 分支按坐标匹配）。现在只数这个词本身上的。
 * v6 搬运时这个图标整个被注释掉了（「TODO reload」），2026-10-15 恢复。
 */
const WbwPaliDiscussionIcon = ({ data, studio }: IWidget) => {
  const userInCourse = useAppSelector(courseUser);
  const currUser = useAppSelector(currentUser);

  // 学员看别人的逐词 channel：只显示自己发起的
  const onlyMe =
    userInCourse?.role === "student" && studio?.id !== currUser?.id;

  return (
    <DiscussionButton
      initCount={data.hasComment ? 1 : 0}
      hideCount
      hideInZero
      onlyMe={onlyMe}
      resId={data.uid}
      resType="wbw"
    />
  );
};

export default WbwPaliDiscussionIcon;
