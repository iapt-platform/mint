import { Space } from "antd";
import { Link } from "react-router-dom";

import HeadBar from "../../../components/library/HeadBar";
import Footer from "../../../components/library/Footer";

const Widget = () => {
	// TODO
  return (
    <div>
		<HeadBar />
      <div>文集首页</div>
	  <div>
	  	<Space>
			<Link to="/anthology/show/12345">文集1</Link>
			<Link to="/article/show/23456">文章1</Link>
		</Space>
	  </div>
		<Footer />
    </div>
  );
};

export default Widget;
