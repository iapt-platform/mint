import { useParams } from "react-router";

import TagCreate from "../../../components/tag/TagCreate";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const { tagId } = useParams(); //url 参数
  const user = useAppSelector(currentUser);
  const studioName = user?.realName;

  return <TagCreate studio={studioName} tagId={tagId} />;
};

export default Widget;
