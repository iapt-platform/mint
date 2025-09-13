import { useParams } from "react-router-dom";
import { ChatContainer } from "../../../components/chat/ChatContainer";

const AI = () => {
  const { id } = useParams();

  return <ChatContainer chatId={id ?? ""} />;
};

export default AI;
