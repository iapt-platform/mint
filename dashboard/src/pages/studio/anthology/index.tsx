import { useParams } from "react-router-dom";
import { ProTable } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { Link } from "react-router-dom";
import { Button, Layout, Space, Table } from "antd";
import { EllipsisOutlined } from "@ant-design/icons";
import { PlusOutlined } from "@ant-design/icons";

import HeadBar from "../../../components/studio/HeadBar";
import LeftSider from "../../../components/studio/LeftSider";
import Footer from "../../../components/studio/Footer";

const { Content } = Layout;

interface IItem {
	id: number;
	title: string;
	subtitle: string;
	tag: string;
	articles: number;
	createdAt: number;
}

const Widget = () => {
	const intl = useIntl();
	const { studioname } = useParams();
	return (
		<Layout>
			<HeadBar />
			<Layout>
				<LeftSider selectedKeys="anthology" />
				<Content>
					<Layout>{studioname}</Layout>
					<ProTable<IItem>
						columns={[
							{
								title: intl.formatMessage({ id: "dict.fields.sn.label" }),
								dataIndex: "id",
								key: "id",
								width: 80,
								search: false,
							},
							{
								title: intl.formatMessage({ id: "forms.fields.title.label" }),
								dataIndex: "title",
								key: "title",
								tip: "过长会自动收缩",
								ellipsis: true,
								render: (text, row, index, action) => {
									return <Link to="edit/12345">{row.title}</Link>;
								},
							},
							{
								title: intl.formatMessage({ id: "forms.fields.subtitle.label" }),
								dataIndex: "subtitle",
								key: "type",
								search: false,
							},
							{
								title: intl.formatMessage({ id: "forms.fields.power.label" }),
								dataIndex: "tag",
								key: "tag",
								search: false,
								filters: true,
								onFilter: true,
								valueEnum: {
									all: { text: "全部", status: "Default" },
									30: { text: "拥有者", status: "Success" },
									20: { text: "可编辑", status: "Processing" },
									10: { text: "只读", status: "Default" },
								},
							},
							{
								title: intl.formatMessage({ id: "article.fields.article.count.label" }),
								dataIndex: "articles",
								key: "articles",
								search: false,
								sorter: (a, b) => a.articles - b.articles,
							},
							{
								title: intl.formatMessage({ id: "forms.fields.created-at.label" }),
								key: "created-at",
								width: 200,
								search: false,
								dataIndex: "createdAt",
								valueType: "date",
								sorter: (a, b) => a.createdAt - b.createdAt,
							},
							{
								title: "操作",
								key: "option",
								width: 120,
								valueType: "option",
								render: () => [
									<Button key="link">编辑</Button>,
									<Button key="more">
										<EllipsisOutlined />
									</Button>,
								],
							},
						]}
						rowSelection={{
							// 自定义选择项参考: https://ant.design/components/table-cn/#components-table-demo-row-selection-custom
							// 注释该行则默认不显示下拉选项
							selections: [Table.SELECTION_ALL, Table.SELECTION_INVERT],
						}}
						tableAlertRender={({ selectedRowKeys, selectedRows, onCleanSelected }) => (
							<Space size={24}>
								<span>
									已选 {selectedRowKeys.length} 项
									<Button type="link" style={{ marginInlineStart: 8 }} onClick={onCleanSelected}>
										取消选择
									</Button>
								</span>
							</Space>
						)}
						tableAlertOptionRender={() => {
							return (
								<Space size={16}>
									<Button type="link">批量删除</Button>
								</Space>
							);
						}}
						request={async (params = {}, sorter, filter) => {
							// TODO
							console.log(params, sorter, filter);

							const size = params.pageSize || 20;
							return {
								total: 1 << 12,
								success: true,
								data: Array.from(Array(size).keys()).map((x) => {
									const id = ((params.current || 1) - 1) * size + x + 1;

									var it: IItem = {
										id,
										title: `title ${id}`,
										subtitle: `subtitle ${id}`,
										tag: ((Math.floor(Math.random() * 3) + 1) * 10).toString(),
										articles: Math.floor(Math.random() * 40),
										createdAt: Date.now() - Math.floor(Math.random() * 2000000000),
									};
									return it;
								}),
							};
						}}
						rowKey="id"
						bordered
						pagination={{
							showQuickJumper: true,
							showSizeChanger: true,
						}}
						search={false}
						options={{
							search: true,
						}}
						headerTitle={intl.formatMessage({ id: "dict" })}
						toolBarRender={() => [
							<Button key="button" icon={<PlusOutlined />} type="primary">
								新建
							</Button>,
						]}
					/>
				</Content>
			</Layout>
			<Footer />
		</Layout>
	);
};

export default Widget;
