import { Space } from "antd";
import HeadBar from "../../../components/library/HeadBar";
import Footer from "../../../components/library/Footer";

const Widget = () => {
	// TODO
  return (
    <div>
		<HeadBar selectedKeys="palicanon" />
      <div>圣典分类</div>
      <div>
		<Space>主显示区</Space>
      </div>
		<Footer />
    </div>
  );
};

export default Widget;
