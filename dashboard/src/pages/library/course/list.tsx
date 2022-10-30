import { Link } from "react-router-dom";

const Widget = () => {
	// TODO
	return (
		<div>
			<div>课程首页</div>
			<div>
				<Link to="/course/show/12345">课程1</Link>
				<Link to="/course/show/23456">课程2</Link>
			</div>
		</div>
	);
};

export default Widget;
