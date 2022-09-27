import React from 'react';
import { Affix, Layout, Menu, Breadcrumb, Table, Tag, Space, Pagination, message, notification, Anchor, List, Avatar } from "antd";
import { Row, Col } from "antd";
import { UserOutlined, LaptopOutlined, NotificationOutlined } from "@ant-design/icons";
import { Footer } from "antd/lib/layout/layout";
import { useState } from 'react';
import { MessageOutlined, LikeOutlined, StarOutlined } from '@ant-design/icons';
import { Link, history } from 'umi';

const { Header, Content, Sider } = Layout;

export default ({ match }) => {
	return (

		<Layout>
			<Layout>
				<h1>welcome to  {match.params.username} zone</h1>
			</Layout>
			<Header className="header">
				<Menu theme="dark" mode="horizontal" defaultSelectedKeys={['1']}>
					<Menu.Item key="1" >首页</Menu.Item>
					<Menu.Item key="2" onClick={() => history.push('/' + match.params.username + '/translation')}>译文</Menu.Item>
					<Menu.Item key="3" onClick={() => history.push('/' + match.params.username + '/course')}>课程</Menu.Item>
					<Menu.Item key="4" onClick={() => history.push('/' + match.params.username + '/article')}>文集</Menu.Item>
				</Menu>
			</Header>

		</Layout>

	)
}
