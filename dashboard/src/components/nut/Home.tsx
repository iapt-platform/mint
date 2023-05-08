import ReactMarkdown from "react-markdown";
import code_png from "../../assets/nut/code.png";

import MarkdownForm from "./MarkdownForm";
import MarkdownShow from "./MarkdownShow";
import FontBox from "./FontBox";
import TreeTest from "./TreeTest";

import { Layout } from "antd";
import CaseFormula from "../template/Wbw/CaseFormula";

const Widget = () => {
  return (
    <Layout>
      <h1>Home</h1>
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
