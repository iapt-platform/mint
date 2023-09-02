import ReactMarkdown from "react-markdown";
import code_png from "../../assets/nut/code.png";

import MarkdownForm from "./MarkdownForm";
import MarkdownShow from "./MarkdownShow";
import FontBox from "./FontBox";
import TreeTest from "./TreeTest";

import { Layout, Typography } from "antd";
import CaseFormula from "../template/Wbw/CaseFormula";
import EditableLabel from "../general/EditableLabel";
import Tree from "./test/Tree";
const { Paragraph } = Typography;

const Widget = () => {
  return (
    <Layout>
      <h1>Home</h1>
      <Paragraph style={{ width: 200 }}>
        <EditableLabel value="测试意思" />
      </Paragraph>
      <CaseFormula />
      <h2>TreeTest</h2>
      <Tree />

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
