import MsgContainer from "./MsgContainer";
import type { IAiModel } from "../../api/ai";
import Marked from "../general/Marked";
import User from "../auth/User";

interface IWidget {
  model?: IAiModel;
  text?: string;
}
const MsgTyping = ({ model, text }: IWidget) => {
  return (
    <MsgContainer>
      <div>
        <User {...model?.user} />
      </div>
      <div>
        <Marked text={text} />
      </div>
    </MsgContainer>
  );
};

export default MsgTyping;
