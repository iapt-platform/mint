import AttachmentList from "../../../components/attachment/AttachmentList";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const user = useAppSelector(currentUser);
  const studioName = user?.realName;

  return (
    <div>
      <title>driver</title>
      <AttachmentList studioName={studioName} />
    </div>
  );
};

export default Widget;
