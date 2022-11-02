import { useParams } from "react-router-dom";
import { ProTable } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { Link } from "react-router-dom";
import { Layout, Space, Table } from "antd";
import { PlusOutlined } from "@ant-design/icons";
import type { MenuProps } from "antd";
import { Button, Dropdown, Menu, Popover } from "antd";
import { SearchOutlined } from "@ant-design/icons";

import AnthologyCreate from "../../../components/studio/anthology/AnthologyCreate";

const onMenuClick: MenuProps["onClick"] = (e) => {
	console.log("click", e);
};

const menu = (
	<Menu
		onClick={onMenuClick}
		items={[
			{
				key: "1",
				label: "在藏经阁中打开",
				icon: <SearchOutlined />,
			},
			{
				key: "2",
				label: "分享",
				icon: <SearchOutlined />,
			},
			{
				key: "3",
				label: "详情",
				icon: <SearchOutlined />,
			},
		]}
	/>
);

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
	const anthologyCreate = <AnthologyCreate studio={studioname} />;
	return (
		<>
			<Layout>{studioname}</Layout>
			<ProTable<IItem>
				columns={[
					{
						title: intl.formatMessage({
							id: "dict.fields.sn.label",
						}),
						dataIndex: "id",
						key: "id",
						width: 50,
						search: false,
					},
					{
						title: intl.formatMessage({
							id: "forms.fields.title.label",
						}),
						dataIndex: "title",
						key: "title",
						tip: "过长会自动收缩",
						ellipsis: true,
						render: (text, row, index, action) => {
							return (
								<div>
									<div>
										<Link to="edit/12345">{row.title}</Link>
									</div>
									<div>{row.subtitle}</div>
								</div>
							);
						},
					},
					{
						title: intl.formatMessage({
							id: "forms.fields.power.label",
						}),
						dataIndex: "tag",
						key: "tag",
						width: 100,
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
						title: intl.formatMessage({
							id: "article.fields.article.count.label",
						}),
						dataIndex: "articles",
						key: "articles",
						width: 100,
						search: false,
						sorter: (a, b) => a.articles - b.articles,
					},
					{
						title: intl.formatMessage({
							id: "forms.fields.created-at.label",
						}),
						key: "created-at",
						width: 100,
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
						render: (text, row, index, action) => [
							<Dropdown.Button
								type="link"
								key={index}
								overlay={menu}
							>
								{intl.formatMessage({
									id: "buttons.edit",
								})}
							</Dropdown.Button>,
						],
					},
				]}
				rowSelection={{
					// 自定义选择项参考: https://ant.design/components/table-cn/#components-table-demo-row-selection-custom
					// 注释该行则默认不显示下拉选项
					selections: [Table.SELECTION_ALL, Table.SELECTION_INVERT],
				}}
				tableAlertRender={({
					selectedRowKeys,
					selectedRows,
					onCleanSelected,
				}) => (
					<Space size={24}>
						<span>
							{intl.formatMessage({ id: "buttons.selected" })}{" "}
							{selectedRowKeys.length}
							<Button
								type="link"
								style={{ marginInlineStart: 8 }}
								onClick={onCleanSelected}
							>
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
							const id =
								((params.current || 1) - 1) * size + x + 1;

							var it: IItem = {
								id,
								title: `title ${id}`,
								subtitle: `subtitle ${id}`,
								tag: (
									(Math.floor(Math.random() * 3) + 1) *
									10
								).toString(),
								articles: Math.floor(Math.random() * 40),
								createdAt:
									Date.now() -
									Math.floor(Math.random() * 2000000000),
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
				toolBarRender={() => [
					<Popover
						content={anthologyCreate}
						title="new article"
						placement="bottomRight"
					>
						<Button
							key="button"
							icon={<PlusOutlined />}
							type="primary"
						>
							{intl.formatMessage({ id: "buttons.create" })}
						</Button>
					</Popover>,
				]}
			/>
		</>
	);
};

export default Widget;
