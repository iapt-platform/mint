import { ChatContainer } from "../../../components/chat/ChatContainer";

const AI = () => {
  return (
    <div>
      <div
        style={{
          width: 1000,
          marginLeft: "auto",
          marginRight: "auto",
        }}
      >
        <ChatContainer chatId={""} />
      </div>
    </div>
  );
};

export default AI;
