import { useIntl } from "react-intl";
import AttachmentList from "../../../components/attachment/AttachmentList";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const user = useAppSelector(currentUser);
  const studioName = user?.realName;
  const intl = useIntl();

  return (
    <div>
      <title>
        {intl.formatMessage({ id: "columns.studio.attachment.title" })}
      </title>
      <AttachmentList studioName={studioName} />
    </div>
  );
};

export default Widget;
