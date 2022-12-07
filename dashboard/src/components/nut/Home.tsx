import ReactMarkdown from "react-markdown";
import code_png from "../../assets/nut/code.png";
import ChannelPicker from "../channel/ChannelPicker";
import MdView from "../template/MdView";
import { IWbw } from "../template/Wbw/WbwWord";
import WbwSent from "../template/WbwSent";

import MarkdownForm from "./MarkdownForm";
import MarkdownShow from "./MarkdownShow";
import FontBox from "./FontBox";
import DemoForm from "./Form";

const Widget = () => {
  let wbwData: IWbw[] = [];
  const valueMake = (value: string) => {
    return { value: value, status: 3 };
  };
  const valueMake2 = (value: string[]) => {
    return { value: value, status: 3 };
  };
  for (let index = 0; index < 20; index++) {
    wbwData.push({
      word: valueMake("Word" + index),
      real: valueMake("word" + index),
      meaning: { value: ["意思" + index], status: 3 },
      factors: valueMake("word+word"),
      factorMeaning: valueMake("mean+mean"),
      type: valueMake(".n."),
      grammar: valueMake(".m.$.sg.$.nom."),
      case: valueMake2(["n", "m", "sg", "nom"]),
      confidence: 1,
    });
  }
  return (
    <div>
      <h1>Home</h1>
      <h2>wbw</h2>
      <div style={{ width: 700 }}>
        <WbwSent
          data={wbwData}
          display="block"
          fields={{
            meaning: true,
            factors: true,
            factorMeaning: true,
            case: true,
          }}
        />
      </div>
      <h2>channel picker</h2>
      <div style={{ width: 1000 }}>
        <ChannelPicker type="chapter" articleId="168-915" />
      </div>
      <h2>MdView test</h2>
      <MdView html="<h1 name='h1'>hello<MdTpl name='term'/></h1>" />

      <br />
      <DemoForm />
      <br />
      <FontBox />
      <br />

      <MarkdownShow body="- Hello, **《mint》**!" />
      <br />
      <h3>Form</h3>
      <MarkdownForm />
      <br />
      <img alt="code" src={code_png} />
      <div>
        <ReactMarkdown>*This* is text with `quote`</ReactMarkdown>
      </div>
    </div>
  );
};

export default Widget;
