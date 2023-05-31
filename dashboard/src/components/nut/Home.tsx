import ReactMarkdown from "react-markdown";
import code_png from "../../assets/nut/code.png";

import MarkdownForm from "./MarkdownForm";
import MarkdownShow from "./MarkdownShow";
import FontBox from "./FontBox";
import TreeTest from "./TreeTest";

import { Layout } from "antd";
import CaseFormula from "../template/Wbw/CaseFormula";
import { IWbw } from "../template/Wbw/WbwWord";
import { WbwSentCtl } from "../template/WbwSent";

const Widget = () => {
  let wbwData: IWbw[] = [];
  const valueMake = (value: string) => {
    return { value: value, status: 3 };
  };

  for (let index = 0; index < 20; index++) {
    wbwData.push({
      book: 0,
      para: 1,
      sn: [index],
      word: valueMake("Word" + index),
      real: valueMake("word" + index),
      meaning: { value: "意思" + index, status: 3 },
      factors: valueMake("word+word"),
      confidence: 1,
    });
  }

  return (
    <Layout>
      <h1>Home</h1>
      <div style={{ width: 700 }}>
        <WbwSentCtl
          book={1}
          para={1}
          wordStart={1}
          wordEnd={10}
          channelId={"dd"}
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
      <CaseFormula />
      <h2>TreeTest</h2>
      <TreeTest />

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
    </Layout>
  );
};

export default Widget;
