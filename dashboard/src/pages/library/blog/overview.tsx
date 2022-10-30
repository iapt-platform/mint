import { useParams } from "react-router-dom";
import { Row, Col } from "antd";
import { Affix } from "antd";
import BlogNav from "../../../components/library/blog/BlogNav";
import Profile from "../../../components/library/blog/Profile";
import AuthorTimeLine from "../../../components/library/blog/TimeLine";
import TopArticles from "../../../components/library/blog/TopArticles";

const Widget = () => {
	// TODO
	const { studio } = useParams(); //url 参数

	return (
		<>
			<Affix offsetTop={0}>
				<BlogNav selectedKey="overview" studio={studio ? studio : ""} />
			</Affix>

			<Row>
				<Col flex="300px">
					<Profile />
				</Col>

				<Col flex="900px">
					<div>
						<TopArticles studio={studio ? studio : ""} />
					</div>
					<div>
						<AuthorTimeLine />
					</div>
				</Col>
			</Row>
		</>
	);
};

export default Widget;
