import ReactMarkdown from "react-markdown";
import code_png from "../../assets/nut/code.png";

import MarkdownForm from "./MarkdownForm";
import MarkdownShow from "./MarkdownShow";
import FontBox from "./FontBox";
import TreeTest from "./TreeTest";

import { Layout } from "antd";
import WbwDetailFm from "../template/Wbw/WbwDetailFm";

const Widget = () => {
  return (
    <Layout>
      <h1>Home</h1>
      <WbwDetailFm
        factors={["abhi", "dhamma"]}
        initValue={[]}
        onChange={(value: string[]) => {
          console.log("fm change", value);
        }}
      />
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
