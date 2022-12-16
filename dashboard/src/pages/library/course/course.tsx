import { Link } from "react-router-dom";
import { useParams } from "react-router-dom";

const Widget = () => {
	// TODO
	const { courseid } = useParams(); //url 参数
	return (
		<div>
			<div>课程{courseid} 详情</div>
			<div>
				<Link to="/course/lesson/12345">lesson 1</Link>
				<Link to="/course/lesson/23456">lesson 2</Link>
			</div>
		</div>
	);
};

export default Widget;
