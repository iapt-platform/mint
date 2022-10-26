import { Space } from "antd";
import { useParams, Link } from "react-router-dom";
import HeadBar from "../../../components/library/HeadBar";
import FooterBar from "../../../components/library/FooterBar";
const Widget = () => {
	// TODO
	const { courseid } = useParams(); //url 参数
	return (
		<div>
			<HeadBar selectedKeys="course" />
			<div>课程{courseid} 详情</div>
			<div>
				<Space>
					<Link to="/course/lesson/12345">lesson 1</Link>
					<Link to="/course/lesson/23456">lesson 2</Link>
				</Space>
			</div>
			<FooterBar />
		</div>
	);
};

export default Widget;
