import { Space } from "antd";
import { useParams } from "react-router-dom";

import HeadBar from "../../../components/library/HeadBar";
import FooterBar from "../../../components/library/FooterBar";

const Widget = () => {
	// TODO
	const { word } = useParams(); //url 参数
	return (
		<div>
			<HeadBar />
			<div>术语百科 单词-{word}</div>
			<div>
				<Space>主显示区</Space>
			</div>
			<FooterBar />
		</div>
	);
};

export default Widget;
