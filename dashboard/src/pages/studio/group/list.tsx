import { useParams, Link } from "react-router-dom";
import { useIntl } from "react-intl";
import { useState } from "react";
import { ProList } from "@ant-design/pro-components";
import { Space, Tag, Button, Layout, Breadcrumb, Popover } from "antd";
import { PlusOutlined } from "@ant-design/icons";

import HeadBar from "../../../components/studio/HeadBar";
import LeftSider from "../../../components/studio/LeftSider";
import Footer from "../../../components/studio/Footer";
import GroupCreate from "../../../components/studio/group/GroupCreate";

const { Content } = Layout;

const defaultData = [
	{
		id: "1",
		name: "IAPT巴利语学习营",
		tag: [
			{ title: "巴利语", color: "blue" },
			{ title: "大金塔", color: "yellow" },
			{ title: "拥有者", color: "success" },
		],
		image: "https://gw.alipayobjects.com/zos/antfincdn/efFD%24IOql2/weixintupian_20170331104822.jpg",
		description: "我是一条测试的描述",
	},
	{
		id: "2",
		name: "初级巴利语入门",
		tag: [
			{ title: "巴利语", color: "blue" },
			{ title: "管理员", color: "processing" },
		],
		image: "https://gw.alipayobjects.com/zos/antfincdn/efFD%24IOql2/weixintupian_20170331104822.jpg",
		description: "我是一条测试的描述",
	},
	{
		id: "3",
		name: "大金塔寺学习小组",
		tag: [
			{ title: "大金塔", color: "yellow" },
			{ title: "成员", color: "default" },
		],
		image: "https://gw.alipayobjects.com/zos/antfincdn/efFD%24IOql2/weixintupian_20170331104822.jpg",
		description: "我是一条测试的描述",
	},
	{
		id: "4",
		name: "趣向涅槃之道第一册翻译组",
		tag: [
			{ title: "大金塔", color: "yellow" },
			{ title: "成员", color: "default" },
		],
		image: "https://gw.alipayobjects.com/zos/antfincdn/efFD%24IOql2/weixintupian_20170331104822.jpg",
		description: "我是一条测试的描述",
	},
];
type DataItem = typeof defaultData[number];

const Widget = () => {
	const intl = useIntl(); //i18n
	const { studioname } = useParams(); //url 参数
	const [dataSource, setDataSource] = useState<DataItem[]>(defaultData);
	const linkStudio = `/studio/${studioname}`;
	const linkGroup = `${linkStudio}/group`;
	return (
		<Layout>
			<HeadBar />
			<Layout>
				<LeftSider selectedKeys="group" />
				<Content>
					<Breadcrumb>
						<Breadcrumb.Item>
							<Link to={linkStudio}>
								{intl.formatMessage({
									id: "columns.studio.title",
								})}
							</Link>
						</Breadcrumb.Item>
						<Breadcrumb.Item>
							{intl.formatMessage({
								id: "columns.studio.collaboration.title",
							})}
						</Breadcrumb.Item>
						<Breadcrumb.Item>
							<Link to={linkGroup}>
								{intl.formatMessage({
									id: "columns.studio.group.title",
								})}
							</Link>
						</Breadcrumb.Item>
						<Breadcrumb.Item>列表</Breadcrumb.Item>
					</Breadcrumb>
					<Layout>
						<ProList<DataItem>
							rowKey="id"
							headerTitle="群组列表"
							dataSource={dataSource}
							showActions="hover"
							editable={{
								onSave: async (key, record, originRow) => {
									console.log(key, record, originRow);
									return true;
								},
							}}
							onDataSourceChange={setDataSource}
							toolBarRender={() => [
								<Popover
									content={
										<GroupCreate studio={studioname} />
									}
									placement="bottomRight"
								>
									<Button
										key="button"
										icon={<PlusOutlined />}
										type="primary"
									>
										{intl.formatMessage({
											id: "buttons.create",
										})}
									</Button>
								</Popover>,
							]}
							metas={{
								title: {
									dataIndex: "name",
									render: (text, row, index, action) => {
										return (
											<Link to={row.id}>{row.name}</Link>
										);
									},
								},
								avatar: {
									dataIndex: "image",
									editable: false,
								},
								description: {
									dataIndex: "description",
								},
								content: {
									dataIndex: "content",
									editable: false,
								},
								subTitle: {
									render: (text, row, index, action) => {
										const showtag = row.tag.map(
											(item, id) => {
												return (
													<Tag
														color={item.color}
														key={id}
													>
														{item.title}
													</Tag>
												);
											}
										);
										return (
											<Space size={0}>{showtag}</Space>
										);
									},
								},
								actions: {
									render: (text, row, index, action) => [
										<Button
											onClick={() => {
												action?.startEditable(row.id);
											}}
											key="link"
										>
											{intl.formatMessage({
												id: "buttons.edit",
											})}
										</Button>,
									],
								},
							}}
						/>
					</Layout>
				</Content>
			</Layout>
			<Footer />
		</Layout>
	);
};

export default Widget;
