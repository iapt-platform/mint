import { Space } from "antd";
import { useParams } from "react-router-dom";

import HeadBar from "../../../components/library/HeadBar";
import Footer from "../../../components/library/Footer";

const Widget = () => {
	// TODO
	const { article_id } = useParams();//url 参数

  return (
    <div>
		<HeadBar />
      <div>文章阅读器{article_id}</div>
      <div>
		<Space>主显示区</Space>
      </div>
		<Footer />
    </div>
  );
};

export default Widget;
