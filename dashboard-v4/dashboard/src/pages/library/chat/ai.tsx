import { useParams } from "react-router-dom";
import { ChatContainer } from "../../../components/chat/ChatContainer";

const AI = () => {
  const { id } = useParams();

  return (
    <div>
      <div style={{ width: 1000, marginLeft: "auto", marginRight: "auto" }}>
        <ChatContainer chatId={id ?? ""} />
      </div>
    </div>
  );
};

export default AI;
