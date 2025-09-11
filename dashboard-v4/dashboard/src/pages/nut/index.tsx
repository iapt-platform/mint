import FooterBar from "../../components/library/FooterBar";

import HeadBar from "../../components/library/HeadBar";
import AIChatComponent from "../../components/chat/AiChat";
import { Content } from "antd/lib/layout/layout";
import TreeText from "../../components/article/TreeText";
import { ChatContainer } from "../../components/chat/ChatContainer";

const Widget = () => {
  return (
    <div>
      <HeadBar />
      <Content>
        <div>Home Page</div>
        <TreeText
          type="chapter"
          rootId="140-92"
          channelsId={["7fea264d-7a26-40f8-bef7-bc95102760fb"]}
        />
        <div
          style={{
            backgroundColor: "white",
            color: "black",
            border: "2px solid red",
            margin: 10,
          }}
        >
          <ChatContainer chatId={"123"} />
        </div>
      </Content>
      <FooterBar />
    </div>
  );
};

export default Widget;
