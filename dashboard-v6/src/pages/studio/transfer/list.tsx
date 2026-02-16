import { useParams } from "react-router-dom";
import TransferList from "../../../components/transfer/TransferList";

const Widget = () => {
  const { studioname } = useParams();
  return (
    <>
      <TransferList studioName={studioname} />
    </>
  );
};

export default Widget;
