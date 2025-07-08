import FooterBar from "../../components/library/FooterBar";

import HeadBar from "../../components/library/HeadBar";
import AIChatComponent from "../../components/chat/AiChat";
import { Content } from "antd/lib/layout/layout";

const Widget = () => {
  return (
    <div>
      <HeadBar />
      <Content>
        <div>Home Page</div>
        <AIChatComponent />
      </Content>
      <FooterBar />
    </div>
  );
};

export default Widget;
