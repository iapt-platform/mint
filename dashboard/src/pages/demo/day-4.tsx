import { Affix, Layout, Menu, Breadcrumb, Table, Tag, Space, Pagination, message, notification, Anchor } from "antd";
import { Row, Col } from "antd";
import { UserOutlined, LaptopOutlined, NotificationOutlined } from "@ant-design/icons";
import { Footer } from "antd/lib/layout/layout";
import { useState } from 'react';
import { WidgetCommitNofifiction } from '@/components/demo'


const { SubMenu } = Menu;
const { Header, Content, Sider } = Layout;
const { Link } = Anchor;

message.config({
	maxCount: 4
});
//表数据
const dataSource = [
	{
		key: '1',
		name: 'name1',
		age: 32,
		adress: "西湖南路"
	},
	{
		key: '2',
		name: 'name2',
		age: 34,
		adress: '西湖公园'
	},
	{
		key: '3',
		name: 'name1',
		age: 32,
		adress: "西湖南路"
	},
	{
		key: '4',
		name: 'name2',
		age: 34,
		adress: '西湖公园'
	},
	{
		key: '5',
		name: 'name1',
		age: 32,
		adress: "西湖南路"
	},
	{
		key: '6',
		name: 'name2',
		age: 34,
		adress: '西湖公园'
	}
]
//表头
const columns = [
	{
		title: 'Id',
		dataIndex: 'id',
		key: 'id',
		render: text => <a>{text}</a>,
	},
	{
		title: 'user_id',
		dataIndex: 'user_id',
		key: 'user_id',
	},
	{
		title: "title",
		dataIndex: 'title',
		key: 'title',
	}
]

function handleClick(e) {
	console.log('click', e);
	ntfOpen(e.key);
}
function pageChange(page: number, pagesize?: number | undefined) {
	message.info("page:" + page);
	if (pagesize) {
		message.error("pagesize:" + pagesize);
	}
}
function ntfOpen(msg: string) {
	const args = {
		message: "title",
		description: msg,
		duration: 5,
	};
	notification.open(args);

}
export default () => {
	const [top, setTop] = useState(0);
	const [bottom, setBottom] = useState(10);
	const [commitStatus, setcommitStatus] = useState(false);
	const [commitTime, setcommitTime] = useState(0);
	const [commitMsg, setcommitMsg] = useState("失败");

	const [tableData, setTableData] = useState();


	function getTableData(){
		fetch('https://gorest.co.in/public-api/posts')
			.then(function (response) {
				console.log("ajex:", response);
				return response.json();
			})
			.then(function (myJson) {
				console.log("ajex",myJson.data);
				setTableData(myJson.data);
			});		
	}
	function pageChange(page: number, pagesize?: number | undefined) {
		setcommitTime(page);
		message.info("page:" + page);
		if (pagesize) {
			message.error("pagesize:" + pagesize);
		}
	}

	return (
		<Layout>
			<Header className="header">
				<div className="logo" />

				<Menu onClick={handleClick} theme="dark" mode="horizontal" defaultSelectedKeys={['2']}>
					<Menu.Item key="0">
						<WidgetCommitNofifiction time={commitTime} message={commitMsg} successful={commitStatus} />
					</Menu.Item>
					<Menu.Item key="1" onClick={getTableData}>Palicanon</Menu.Item>
					<Menu.Item key="2">Course</Menu.Item>
					<Menu.Item key="3">nav 3</Menu.Item>
					<SubMenu key="submenu" icon={<UserOutlined />} title="Others">
						<Menu.ItemGroup title="group1">
							<Menu.Item key="4">option1</Menu.Item>
							<Menu.Item key="5">option2</Menu.Item>
							<Menu.Item key="6">option3</Menu.Item>
						</Menu.ItemGroup>
						<Menu.ItemGroup title="group2">
							<Menu.Item key="7">option1</Menu.Item>
							<Menu.Item key="8">option2</Menu.Item>
							<Menu.Item key="9">option3</Menu.Item>
						</Menu.ItemGroup>
					</SubMenu>
				</Menu>
			</Header>
			<Layout>
				<Affix offsetTop={top}>
					<Sider className="site-layout-background">
						<Menu
							mode="inline"
							defaultSelectedKeys={['1']}
							defaultOpenKeys={['sub1']}
							style={{ height: '100%', borderRight: 0 }}
						>
							<SubMenu key="sub1" icon={<UserOutlined />} title="subnav 1">
								<Menu.Item key="1">option1</Menu.Item>
								<Menu.Item key="2">option2</Menu.Item>
								<Menu.Item key="3">option3</Menu.Item>
								<Menu.Item key="4">option4</Menu.Item>
							</SubMenu>
							<SubMenu key="sub2" icon={<UserOutlined />} title="subnav 2">
								<Menu.Item key="5">option1</Menu.Item>
								<Menu.Item key="6">option2</Menu.Item>
								<Menu.Item key="7">option3</Menu.Item>
								<Menu.Item key="8">option4</Menu.Item>
							</SubMenu>
						</Menu>
					</Sider>
				</Affix>

				<Layout style={{ padding: '0 24px 24px' }}>
					<Breadcrumb style={{ padding: '0 24px 24px' }}>
						<Breadcrumb.Item>Home</Breadcrumb.Item>
						<Breadcrumb.Item>List</Breadcrumb.Item>
						<Breadcrumb.Item>App</Breadcrumb.Item>
					</Breadcrumb>
					<Content
						className="site-layout-background"
						style={{
							padding: 24,
							margin: 0,
							minHeight: 280,
							width: "100%",
							overflowX: "auto",
						}}>
						<Table dataSource={tableData} columns={columns} />
						<div>搜索结果</div>
						<Pagination defaultCurrent={1} total={54} onChange={pageChange} />
					</Content>
				</Layout>
				<Affix offsetTop={top}>
					<Sider>
						<Anchor>
							<Link href="#aa" title="aa" />
							<Link href="#bb" title="bb" />
							<Link href="#cc" title="cc" />
							<Link href="#dd" title="dd" />
						</Anchor>
					</Sider>
				</Affix>
			</Layout>

			<Footer>
				<Row>
					<Col span={4}>col1</Col>
					<Col span={16}>col2</Col>
					<Col span={4}>col3</Col>
				</Row>
				<Row>
					<Col xs={4} md={5} xl={4}> col4</Col>
					<Col xs={20} md={14} xl={16}> col5 </Col>
					<Col xs={0} md={5} xl={4}> col6 </Col>
				</Row>
			</Footer>
		</Layout>
	);

}

