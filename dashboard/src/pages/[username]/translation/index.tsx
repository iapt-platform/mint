import React, { useEffect } from 'react';
import { Affix, Layout, Menu, Breadcrumb, Table, Tag, Space, Pagination, message, notification, Anchor, List, Avatar } from "antd";
import { Row, Col } from "antd";
import { UserOutlined, LaptopOutlined, NotificationOutlined } from "@ant-design/icons";
import { Footer } from "antd/lib/layout/layout";
import { useState } from 'react';
import { MessageOutlined, LikeOutlined, StarOutlined } from '@ant-design/icons';
import { Link, history } from 'umi';
import { ChapterListItem } from '@/components/chapterlistitem'

const { SubMenu } = Menu;
const { Header, Content, Sider } = Layout;

const it = [
	{ lable: 'menu1', key: 'menu1' }
];
const items = [
	{
		label: '经藏',
		key: 'sutta',
		tag: 'sutta',
		children: [
			{ label: '长部', key: 'sutta-digha', tag: 'sutta,digha' },
			{ label: '中部', key: 'sutta-majjima', tag: 'sutta,majjima' },
		]
	},
	{
		label: '律藏',
		key: 'vinaya',
		tag: 'vinaya',
		children: [
			{ label: '大分别', key: 'vinaya-maha', tag: 'sutta,digha' },
			{ label: '比库尼分别', key: 'vinaya-bhikkhuni', tag: 'sutta,majjima' },
		]
	},
]

export default ({ match }) => {
	const [tableData, setTableData] = useState();


	function getTableData() {
		let url = 'http://127.0.0.1:8000/api/v2/progress?view=studio&name=' + match.params.username;
		fetch(url)
			.then(function (response) {
				console.log("ajex:", response);
				return response.json();
			})
			.then(function (myJson) {
				console.log("ajex", myJson.data);
				for (let iterator of myJson.data.rows) {
					if (iterator.title == '') {
						iterator.title = iterator.toc;
					}
					iterator.description = iterator.summary;
					iterator.href = "/app/article/?view=chapter&book=" + iterator.book + "&par=" + iterator.para + '&channel=' + iterator.channel_id;
					iterator.avatar = 'https://joeschmoe.io/api/v1/random';
				}
				setTableData(myJson.data.rows);
			});
	}
	window.onload = function () {
		getTableData();
	}
	return (
		<Layout>
			<Layout>
				<h1>welcome to  {match.params.username} zone</h1>
			</Layout>
			<Header className="header">
				<Menu theme="dark" mode="horizontal" defaultSelectedKeys={['2']}>
					<Menu.Item key="1" onClick={() => history.push('/' + match.params.username)}>首页</Menu.Item>
					<Menu.Item key="2" >译文</Menu.Item>
					<Menu.Item key="3" onClick={() => history.push('/' + match.params.username + '/course')}>课程</Menu.Item>
					<Menu.Item key="4">文集</Menu.Item>
				</Menu>
			</Header>
			<Layout onClick={getTableData}>
				<Affix offsetTop={0}>
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
							items={items}
						/>

					</Sider>
				</Affix>

				<List
					itemLayout="vertical"
					size="large"
					pagination={{
						pageSize: 10,
					}}
					dataSource={tableData}
					renderItem={item => (
						<List.Item>
							<ChapterListItem
								title={item.title}
								summary={item.summary}
								path={item.path}
								url={item.href}
							/>
						</List.Item>
					)
					}
					footer={
						<div>
							<b>ant design</b> footer part
						</div>
					}
				/>

				<Affix offsetTop={0}>
					<Sider
						className="site-layout-background"
						breakpoint="lg"
						collapsedWidth="0"
					>
					</Sider>
				</Affix>
			</Layout>
		</Layout>

	)
}
