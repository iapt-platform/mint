import { useAppSelector } from "../../../hooks";
import { courseUser } from "../../../reducers/course-user";
import { currentUser } from "../../../reducers/current-user";
import { IStudio } from "../../auth/Studio";
import DiscussionButton from "../../discussion/DiscussionButton";
import { IWbw } from "./WbwWord";

interface IWidget {
  data: IWbw;
  studio?: IStudio;
}
const WbwPaliDiscussionIcon = ({ data, studio }: IWidget) => {
  const userInCourse = useAppSelector(courseUser);
  const currUser = useAppSelector(currentUser);

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
    }
  }

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
