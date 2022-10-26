import { Space } from "antd";
import { useParams } from "react-router-dom";
import HeadBar from "../../../components/library/HeadBar";
import FooterBar from "../../../components/library/FooterBar";

const Widget = () => {
	// TODO
	const { lessonid } = useParams(); //url 参数

	return (
		<div>
			<HeadBar selectedKeys="course" />
			<div>课 {lessonid} 详情</div>
			<div>
				<Space>主显示区</Space>
			</div>
			<FooterBar />
		</div>
	);
};

export default Widget;
