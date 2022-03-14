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

//表头
const columns = [
	{
		title: 'word',
		dataIndex: 'word',
		key: 'word',
		render: text => <a>{text}</a>,
	},
	{
		title: '意思',
		dataIndex: 'meaning',
		key: 'meaning',
	},
	{
		title: "其他意思",
		dataIndex: 'other_meaning',
		key: 'other_meaning',
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


	function getTableData(e){
        //let url='https://gorest.co.in/public-api/posts';
        let url='http://127.0.0.1:8000/api/v2/terms?view=word&word=dhamma';
		fetch(url)
			.then(function (response) {
				console.log("ajex:", response);
				return response.json();
			})
			.then(function (myJson) {
				console.log("ajex",myJson.data);
				setTableData(myJson.data.rows);
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

				<Menu onClick={handleClick} theme="dark" mode="horizontal" defaultSelectedKeys={['1']}>
					<Menu.Item key="0">
						<WidgetCommitNofifiction time={commitTime} message={commitMsg} successful={commitStatus} />
					</Menu.Item>
					<Menu.Item key="1" >圣典</Menu.Item>
					<Menu.Item key="2">课程</Menu.Item>
					<Menu.Item key="3">字典</Menu.Item>
					<Menu.Item key="3">文集</Menu.Item>
					<SubMenu key="submenu" icon={<UserOutlined />} title="更多">
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
					<Sider 
                        className="site-layout-background"
                        breakpoint="lg"
                        collapsedWidth="0"
                        onBreakpoint={broken => {
                            console.log(broken);
                        }}
                        onCollapse={(collapsed, type) => {
                            console.log(collapsed, type);
                        }}
                    >
						<Menu
							mode="inline"
							defaultSelectedKeys={['1']}
							defaultOpenKeys={['sub1']}
							style={{ height: '100%', borderRight: 0 }}
                            onClick={getTableData}
						>
							<SubMenu key="sutta" icon={<UserOutlined />} title="经藏">
								<Menu.Item key="dn">长部</Menu.Item>
								<Menu.Item key="mn">中部</Menu.Item>
								<Menu.Item key="sn">相应部</Menu.Item>
								<Menu.Item key="an">增支部</Menu.Item>
								<Menu.Item key="kn">小部</Menu.Item>
							</SubMenu>
							<SubMenu key="vinaya" icon={<UserOutlined />} title="律藏">
								<Menu.Item key="6">分别</Menu.Item>
								<Menu.Item key="7">篇章</Menu.Item>
								<Menu.Item key="8">附录</Menu.Item>
							</SubMenu>
							<SubMenu key="abhidhamma" icon={<UserOutlined />} title="阿毗达摩藏">
								<Menu.Item key="9">法集论</Menu.Item>
								<Menu.Item key="10">option2</Menu.Item>
								<Menu.Item key="11">option3</Menu.Item>
								<Menu.Item key="12">option4</Menu.Item>
							</SubMenu>
							<SubMenu key="others" icon={<UserOutlined />} title="其他">
								<Menu.Item key="9">法集论</Menu.Item>
								<Menu.Item key="10">option2</Menu.Item>
								<Menu.Item key="11">option3</Menu.Item>
								<Menu.Item key="12">option4</Menu.Item>
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

