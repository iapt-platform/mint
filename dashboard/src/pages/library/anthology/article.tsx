import { Space } from "antd";
import { useParams } from "react-router-dom";

const Widget = () => {
	// TODO
	const { article_id } = useParams(); //url 参数

	return (
		<div>
			<div>文章阅读器{article_id}</div>
			<div>
				<Space>主显示区</Space>
			</div>
		</div>
	);
};

export default Widget;
