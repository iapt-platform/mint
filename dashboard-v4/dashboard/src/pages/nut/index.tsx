import FooterBar from "../../components/library/FooterBar";

import HeadBar from "../../components/library/HeadBar";
import AIChatComponent from "../../components/chat/AiChat";
import { Content } from "antd/lib/layout/layout";
import TreeText from "../../components/article/TreeText";

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
        <AIChatComponent />
      </Content>
      <FooterBar />
    </div>
  );
};

export default Widget;
