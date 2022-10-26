import { useNavigate } from "react-router-dom";
import { Layout, Affix, Col, Row } from "antd";
import { Input } from "antd";

const { Content, Header } = Layout;
const { Search } = Input;

const Widget = () => {
	const navigate = useNavigate();

	const onSearch = (value: string) => {
		navigate("/dict/" + value);
	};
	return (
		<Layout>
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
					<Col flex="1260px">最近搜索列表</Col>
					<Col flex="auto"></Col>
				</Row>
			</Content>
		</Layout>
	);
};

export default Widget;
