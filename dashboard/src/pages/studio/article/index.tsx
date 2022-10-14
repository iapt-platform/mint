import { useParams, Link } from "react-router-dom";
import { useIntl } from "react-intl";
import { useState } from "react";

import { Space, Layout, Breadcrumb, Button, Tag, Popover } from "antd";
import { ProList } from "@ant-design/pro-components";
import { CheckCircleOutlined, PlusOutlined } from "@ant-design/icons";

import HeadBar from "../../../components/studio/HeadBar";
import LeftSider from "../../../components/studio/LeftSider";
import Footer from "../../../components/studio/Footer";
import ArticleCreate from "../../../components/studio/article/ArticleCreate";

const { Content } = Layout;

const defaultData = [
	{
		id: "1",
		title: "Nissaya Book List",
		name: "Nissaya Book List",
		tag: [{ title: "公开", color: "success", icon: <CheckCircleOutlined /> }],
		description: "文章概要",
	},
	{
		id: "2",
		title: "初级巴利语入门",
		name: "初级巴利语入门",
		tag: [{ title: "公开", color: "success", icon: <CheckCircleOutlined /> }],
		description: "文章概要",
	},
	{
		id: "3",
		title: "何人有资格接受卡提那衣",
		name: "何人有资格接受卡提那衣",
		tag: [{ title: "公开", color: "success", icon: <CheckCircleOutlined /> }],
		description: "文章概要",
	},
	{
		id: "4",
		title: "Adhiṭṭhana 定名/决意",
		name: "Adhiṭṭhana 定名/决意",
		tag: [{ title: "公开", color: "success", icon: <CheckCircleOutlined /> }],
		description: "文章概要",
	},
];
type DataItem = typeof defaultData[number];

const Widget = () => {
	const intl = useIntl(); //i18n
	const { studioname } = useParams(); //url 参数
	const [dataSource, setDataSource] = useState<DataItem[]>(defaultData);
	const articleCreate = <ArticleCreate studio={studioname} />;
	const linkRead = `/article/show/12345`;
	const linkStudio = `/studio/${studioname}`;

	return (
		<Layout>
			<HeadBar />
			<Layout>
				<LeftSider selectedKeys="article" />
				<Content>
					<Breadcrumb>
						<Breadcrumb.Item>
							<Link to={linkStudio}>{intl.formatMessage({ id: "columns.studio.title" })}</Link>
						</Breadcrumb.Item>
						<Breadcrumb.Item>
							{intl.formatMessage({ id: "columns.studio.collaboration.title" })}
						</Breadcrumb.Item>
						<Breadcrumb.Item>{intl.formatMessage({ id: "columns.studio.article.title" })}</Breadcrumb.Item>
					</Breadcrumb>
					<Layout>
						<ProList<DataItem>
							rowKey="id"
							headerTitle="文章"
							dataSource={dataSource}
							showActions="hover"
							editable={{
								onSave: async (key, record, originRow) => {
									console.log(key, record, originRow);
									return true;
								},
							}}
							onDataSourceChange={setDataSource}
							metas={{
								title: {
									dataIndex: "name",
									render: (text, row, index, action) => {
										const linkEdit = `/studio/${studioname}/article/edit/${row.id}`;

										return <Link to={linkEdit}>{row.name}</Link>;
									},
								},
								description: {
									dataIndex: "description",
									search: false,
								},
								content: {
									dataIndex: "content",
									editable: false,
									search: false,
								},
								subTitle: {
									search: false,
									render: (text, row, index, action) => {
										const showtag = row.tag.map((item, key) => {
											return (
												<Tag color={item.color} icon={item.icon}>
													{item.title}
												</Tag>
											);
										});
										return <Space size={0}>{showtag}</Space>;
									},
								},
								actions: {
									render: (text, row, index, action) => {
										const linkEdit = `/studio/${studioname}/article/edit/${row.id}`;

										return [
											<Link to={linkEdit}>编辑</Link>,
											<Link to={linkRead}>阅读</Link>,
											<Button onClick={() => {}} key="link" danger>
												删除
											</Button>,
											<Button onClick={() => {}} key="link">
												分享
											</Button>,
										];
									},
								},
							}}
							search={{
								filterType: "light",
							}}
							bordered
							pagination={{
								showQuickJumper: true,
								showSizeChanger: true,
							}}
							toolBarRender={() => [
								<Popover content={articleCreate} title="new article" placement="bottomRight">
									<Button key="button" icon={<PlusOutlined />} type="primary">
										{intl.formatMessage({ id: "buttons.create" })}
									</Button>
								</Popover>,
							]}
						/>
					</Layout>
				</Content>
			</Layout>
			<Footer />
		</Layout>
	);
};

export default Widget;
