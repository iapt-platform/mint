import TransferList from "../../../components/transfer/TransferList";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";

const Widget = () => {
  const user = useAppSelector(currentUser);
  const studioName = user?.realName;
  return (
    <>
      <TransferList studioName={studioName} />
    </>
  );
};

export default Widget;
