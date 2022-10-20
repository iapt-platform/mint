import ReactMarkdown from "react-markdown";
import { Tag } from "antd";

import HeadBar from "../../components/library/HeadBar";
import Footer from "../../components/library/Footer";

const Widget = () => {
  return (
    <div>
      <HeadBar />
      <div>Home Page</div>
      <ReactMarkdown>- Hello, **mint**!</ReactMarkdown>

      <div>
        <Tag
          onClick={() => {
            console.log("test tag was clicked");
          }}
        >
          Test
        </Tag>
      </div>
      <Footer />
    </div>
  );
};

export default Widget;
