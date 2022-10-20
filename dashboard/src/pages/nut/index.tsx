import { Tag } from "antd";

import HeadBar from "../../components/library/HeadBar";
import Footer from "../../components/library/Footer";
import Home from "../../components/nut/Home";

const Widget = () => {
  return (
    <div>
      <HeadBar />
      <div>Home Page</div>
      <div>
        <Tag
          onClick={() => {
            console.log("test tag was clicked");
          }}
        >
          Test
        </Tag>
      </div>
      <div>
        <Home />
      </div>
      <Footer />
    </div>
  );
};

export default Widget;
