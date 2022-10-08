import { Space } from "antd";
import { Link } from "react-router-dom";
import HeadBar from "../../../components/library/blog/HeadBar";
import Footer from "../../../components/library/Footer";

const Widget = () => {
	// TODO
  return (
    <div>
		<HeadBar />
      <div>课程首页</div>
	  <div>
	  	<Space>
			<Link to="/course/show/12345">课程1</Link>
			<Link to="/course/show/23456">课程2</Link>
		</Space>
	  </div>
		<Footer />
    </div>
  );
};

export default Widget;
