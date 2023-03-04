import ReactMarkdown from "react-markdown";
import code_png from "../../assets/nut/code.png";

import MarkdownForm from "./MarkdownForm";
import MarkdownShow from "./MarkdownShow";
import FontBox from "./FontBox";
import DemoForm from "./Form";
import TreeTest from "./TreeTest";
import Share from "../share/Share";
import ChannelPicker from "../channel/ChannelPicker";
import { Layout } from "antd";

const Widget = () => {
  return (
    <Layout>
      <h1>Home</h1>
      <ChannelPicker
        type="chapter"
        articleId="168-867_7fea264d-7a26-40f8-bef7-bc95102760fb"
      />
      <div>
        <Share resId="dd" resType="dd" />
      </div>
      <h2>TreeTest</h2>
      <TreeTest />
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
    </Layout>
  );
};

export default Widget;
