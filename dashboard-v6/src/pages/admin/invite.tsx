import { useIntl } from "react-intl";
import InviteList from "../../components/invite/InviteList";

const Widget = () => {
  const intl = useIntl();
  return (
    <>
      <title>{intl.formatMessage({ id: "admin.menu.invite" })}</title>
      {/* 不带 studioName ＝ 全站邀请（view=all） */}
      <InviteList />
    </>
  );
};

export default Widget;
