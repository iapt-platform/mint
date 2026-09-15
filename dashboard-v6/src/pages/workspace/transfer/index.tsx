import { useIntl } from "react-intl";
import TransferList from "../../../components/transfer/TransferList";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const user = useAppSelector(currentUser);
  const studioName = user?.realName;
  const intl = useIntl();
  return (
    <>
      <title>
        {intl.formatMessage({ id: "columns.studio.transfer.title" })}
      </title>
      <TransferList studioName={studioName} />
    </>
  );
};

export default Widget;
