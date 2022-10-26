import { Layout, Affix, Row, Col } from "antd";

import HeadBar from "../../../components/library/HeadBar";
import FooterBar from "../../../components/library/FooterBar";
import ChannelList from "../../../components/channel/ChannelList";
import BookTree from "../../../components/corpus/BookTree";
import ChapterFileter from "../../../components/corpus/ChapterFilter";
import ChapterList from "../../../components/corpus/ChapterList";
const { Sider, Content } = Layout;

const Widget = () => {
	// TODO
	return (
		<Row>
			<Col xs={0} xl={6}>
				<Affix offsetTop={0}>
					<Layout style={{ height: "100vh", overflowY: "scroll" }}>
						<BookTree />
						<ChannelList />
					</Layout>
				</Affix>
			</Col>
			<Col xs={24} xl={14}>
				<ChapterFileter />
				<ChapterList />
			</Col>
			<Col xs={0} xl={4}>
				侧边栏 侧边栏 侧边栏 侧边栏 侧边栏
			</Col>
		</Row>
	);
};

export default Widget;
