import { useAppSelector } from "../../hooks";
import { courseUser } from "../../reducers/course-user";
import { courseInfo } from "../../reducers/current-course";
import { currentUser } from "../../reducers/current-user";
import type { IDiscussionCountWbw } from "../../api/Comment";
//import DiscussionButton from "../../discussion/DiscussionButton";

import type { IStudio } from "../../api/Auth";
import type { IWbw } from "../../types/wbw";

interface IWidget {
  data: IWbw;
  studio?: IStudio;
  channelId?: string;
}
const WbwPaliDiscussionIcon = ({ data, studio, channelId }: IWidget) => {
  const userInCourse = useAppSelector(courseUser);
  const currUser = useAppSelector(currentUser);
  const course = useAppSelector(courseInfo);

  let wbw: IDiscussionCountWbw | undefined;
  let onlyMe = false;
  if (userInCourse) {
    if (userInCourse.role === "student") {
      if (studio?.id === currUser?.id) {
        //我自己的wbw channel 显示全部
        onlyMe = false;
      } else {
        //其他channel 只显示自己的
        onlyMe = true;
      }
    } else {
      if (course) {
        if (course.channelId === channelId) {
          wbw = {
            book_id: data.book,
            paragraph: data.para,
            wid: data.sn[0],
          };
        }
      }
    }
  }
  console.debug("wbw discussion", wbw, onlyMe);
  /** TODO reload
 *  return (
    <DiscussionButton
      initCount={data.hasComment ? 1 : 0}
      hideCount
      hideInZero
      onlyMe={onlyMe}
      resId={data.uid}
      resType="wbw"
      wbw={wbw}
    />
  );
 */
  return <></>;
};

export default WbwPaliDiscussionIcon;
