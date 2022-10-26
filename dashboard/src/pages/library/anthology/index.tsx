import { Space, Input } from "antd";
import { Layout, Affix, Col, Row } from "antd";

import HeadBar from "../../../components/library/HeadBar";
import FooterBar from "../../../components/library/FooterBar";
import AnthologyList from "../../../components/article/AnthologyList";
import AnthologStudioList from "../../../components/article/AnthologStudioList";

const { Content, Header } = Layout;
const { Search } = Input;

const Widget = () => {
	// TODO
	const onSearch = (value: string) => console.log(value);
	return (
		<Layout>
			<HeadBar selectedKeys="anthology" />
			<Layout>
				<Header style={{ color: "#FFF" }}>
					<h2>composition</h2>
					<p>
						Make the Pāḷi easy to read <br />
						solution of Pāḷi glossary For translating <br />
						Pāḷi in Group Show the source reference in Pāḷi
					</p>
				</Header>
				<Affix offsetTop={0}>
					<Header style={{ backgroundColor: "gray", height: "3.5em" }}>
						<Row style={{ paddingTop: "0.5em" }}>
							<Col span="8" offset={8}>
								<Search placeholder="input search text" onSearch={onSearch} style={{ width: "100%" }} />
							</Col>
						</Row>
					</Header>
				</Affix>

				<Content>
					<Row>
						<Col flex="auto"></Col>
						<Col flex="1260px">
							<Row>
								<Col span="18">
									<AnthologyList />
								</Col>
								<Col span="6">
									<AnthologStudioList />
								</Col>
							</Row>
						</Col>
						<Col flex="auto"></Col>
					</Row>

					<Space></Space>
				</Content>
			</Layout>

			<FooterBar />
		</Layout>
	);
};

export default Widget;
