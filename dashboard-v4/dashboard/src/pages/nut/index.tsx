import FooterBar from "../../components/library/FooterBar";

import HeadBar from "../../components/library/HeadBar";
import TreeText from "../../components/article/TreeText";
import { ChatContainer } from "../../components/chat/ChatContainer";
import NissayaAligner from "../../components/corpus/NissayaAligner";

const Widget = () => {
  return (
    <div>
      <HeadBar />
      <div>
        <div>Home Page</div>
        <div style={{ backgroundColor: "white" }}>
          <NissayaAligner />
        </div>
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
      </div>
      <FooterBar />
    </div>
  );
};

export default Widget;
