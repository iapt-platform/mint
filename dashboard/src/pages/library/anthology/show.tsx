import { Space } from "antd";
import { useParams } from "react-router-dom";

import HeadBar from "../../../components/library/HeadBar";
import Footer from "../../../components/library/Footer";

const Widget = () => {
	// TODO
	const { anthology_id } = useParams();//url 参数
  return (
    <div>
		<HeadBar selectedKeys="anthology"/>
      <div>文集{anthology_id}详情</div>
      <div>
		<Space>主显示区</Space>
      </div>
		<Footer />
    </div>
  );
};

export default Widget;
