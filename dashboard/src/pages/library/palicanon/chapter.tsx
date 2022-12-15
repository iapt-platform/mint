import { useParams } from "react-router-dom";
import { useNavigate } from "react-router-dom";
import { Layout } from "antd";

import BookViewer, { IParagraph } from "../../../components/corpus/BookViewer";

const { Sider, Content } = Layout;

const Widget = () => {
	// TODO
	const { id } = useParams(); //url 参数
	const navigate = useNavigate();

	const arrPara = id ? id.split("-") : ["0", "0"];
	const para: IParagraph = { book: parseInt(arrPara[0]), para: parseInt(arrPara[1]) };
	console.log(para);
	return (
		<Layout>
			<Sider width={300} style={{ height: "90%", overflowY: "auto" }} breakpoint="lg"></Sider>
			<Content>
				<BookViewer
					para={para}
					onChange={(e: IParagraph) => {
						navigate(`/palicanon/chapter/${e.book}-${e.para}`);
					}}
				/>
			</Content>
		</Layout>
	);
};

export default Widget;
