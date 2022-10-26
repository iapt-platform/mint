import { Layout, Row, Col } from "antd";
import { Button } from "antd";
import ChapterFilterType from "./ChapterFilterType";
import ChapterFilterLang from "./ChapterFilterLang";
import ChapterFilterProgress from "./ChapterFilterProgress";

const Widget = () => {
	return (
		<Layout>
			<Row>
				<Col>
					<ChapterFilterType />
				</Col>
				<Col>
					<ChapterFilterLang />
				</Col>
				<Col>
					<ChapterFilterProgress />
				</Col>
				<Col>
					<Button>Search</Button>
					<Button>Reset</Button>
				</Col>
			</Row>
		</Layout>
	);
};

export default Widget;
