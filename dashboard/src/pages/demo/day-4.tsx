import React from 'react';
import { Affix, Layout, Menu, Breadcrumb, Table, Tag, Space, Pagination, message, notification, Anchor,List, Avatar } from "antd";
import { Row, Col } from "antd";
import { UserOutlined, LaptopOutlined, NotificationOutlined } from "@ant-design/icons";
import { Footer } from "antd/lib/layout/layout";
import { useState } from 'react';
import { WidgetCommitNofifiction } from '@/components/demo'
import { MessageOutlined, LikeOutlined, StarOutlined } from '@ant-design/icons';


const { SubMenu } = Menu;
const { Header, Content, Sider } = Layout;
const { Link } = Anchor;

message.config({
	maxCount: 4
});

const data = [
  {
    title: '梵网经',
  },
  {
    title: '沙门果经',
  },
  {
    title: '盐块经',
  },
  {
    title: '根修习经',
  },
];

const listData = [];
for (let i = 0; i < 23; i++) {
  listData.push({
    href: 'https://ant.design',
    title: `ant design part ${i}`,
    avatar: 'https://joeschmoe.io/api/v1/random',
    description:
      'Ant Design, a design language for background applications, is refined by Ant UED Team.',
    content:
      'We supply a series of design principles, practical patterns and high quality design resources (Sketch and Axure), to help people create their product prototypes beautifully and efficiently.',
  });
}

const IconText = ({ icon, text }) => (
  <Space>
    {React.createElement(icon)}
    {text}
  </Space>
);

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
        let url='http://127.0.0.1:8000/api/v2/progress?view=tag';
		fetch(url)
			.then(function (response) {
				console.log("ajex:", response);
				return response.json();
			})
			.then(function (myJson) {
				console.log("ajex",myJson.data);
                for (let iterator of myJson.data.rows) {
                    if(iterator.title==''){
                        iterator.title = iterator.toc;
                    }
                    iterator.description = iterator.toc;
                    iterator.href="http://127.0.0.1:8000/app/article/?view=chapter&book="+iterator.book+"&par="+iterator.para+'&channel='+iterator.channel_id;
                    iterator.avatar = 'https://joeschmoe.io/api/v1/random';
                }
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
						<Breadcrumb.Item>全部</Breadcrumb.Item>
						<Breadcrumb.Item>经藏</Breadcrumb.Item>
						<Breadcrumb.Item>长部</Breadcrumb.Item>
						<Breadcrumb.Item>……</Breadcrumb.Item>
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
<List
    itemLayout="vertical"
    size="large"
    pagination={{
      onChange: page => {
        console.log(page);
      },
      pageSize: 10,
    }}
    dataSource={tableData}
    footer={
      <div>
        <b>ant design</b> footer part
      </div>
    }
    renderItem={item => (
      <List.Item
        key={item.title}
        actions={[
          <IconText icon={StarOutlined} text={item.progress} key="list-vertical-star-o" />,
          <IconText icon={LikeOutlined} text={item.created_at} key="list-vertical-like-o" />,
          <IconText icon={MessageOutlined} text="2" key="list-vertical-message" />,
        ]}

      >
        <List.Item.Meta
          avatar={<Avatar src={item.avatar} />}
          title={<a href={item.href} target='_blank'>{item.title}</a>}
          description={item.description}
        />
      </List.Item>
    )}
  />
					</Content>
				</Layout>
				<Affix offsetTop={top}>
					<Sider
                    className="site-layout-background"
                        breakpoint="lg"
                        collapsedWidth="0"
                    >
<List
    header={<div>本周最新</div>}
    itemLayout="horizontal"
    dataSource={data}
    renderItem={item => (
      <List.Item>
        <List.Item.Meta
          avatar={<Avatar src="https://joeschmoe.io/api/v1/random" />}
          title={<a href="https://ant.design">{item.title}</a>}
          description="Ant Design, a design language for background applications, is refined by Ant UED Team"
        />
      </List.Item>
    )}
  />
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

