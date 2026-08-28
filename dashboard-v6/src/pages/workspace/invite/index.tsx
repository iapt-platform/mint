import { useIntl } from "react-intl";
import InviteList from "../../../components/invite/InviteList";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const user = useAppSelector(currentUser);
  const studioName = user?.realName;
  const intl = useIntl();

  return (
    <>
      <title>
        {intl.formatMessage({ id: "columns.studio.invite.title" })}
      </title>
      <InviteList studioName={studioName} />
    </>
  );
};

export default Widget;
