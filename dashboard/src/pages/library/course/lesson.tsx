import { useParams } from "react-router-dom";

const Widget = () => {
	// TODO
	const { lessonid } = useParams(); //url 参数

	return (
		<div>
			<div>课 {lessonid} 详情</div>
			<div>
				主显示区
			</div>
		</div>
	);
};

export default Widget;
