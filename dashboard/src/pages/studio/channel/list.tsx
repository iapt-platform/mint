import { useParams } from "react-router-dom";
import { ProTable } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { Link } from "react-router-dom";
import { Layout, Space, Table } from "antd";
import { PlusOutlined } from "@ant-design/icons";
import type { MenuProps } from "antd";
import { Button, Dropdown, Menu, Popover } from "antd";
import { SearchOutlined } from "@ant-design/icons";

import ChannelCreate from "../../../components/studio/channel/ChannelCreate";
import { get } from "../../../request";
import { IApiResponseChannelList } from "../../../components/api/Channel";

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
				label: "删除",
				icon: <SearchOutlined />,
			},
		]}
	/>
);

interface IItem {
	id: number;
	uid: string;
	title: string;
	summary: string;
	type: string;
	publicity: number;
	createdAt: string;
}

const Widget = () => {
	const intl = useIntl();
	const { studioname } = useParams();
	const channelCreate = <ChannelCreate studio={studioname} />;
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
							const link = `/studio/${studioname}/channel/${row.uid}/edit`;
							return <Link to={link}>{row.title}</Link>;
						},
					},
					{
						title: intl.formatMessage({
							id: "forms.fields.summary.label",
						}),
						dataIndex: "summary",
						key: "summary",
						tip: "过长会自动收缩",
						ellipsis: true,
					},
					{
						title: intl.formatMessage({
							id: "forms.fields.type.label",
						}),
						dataIndex: "type",
						key: "type",
						width: 100,
						search: false,
						filters: true,
						onFilter: true,
						valueEnum: {
							all: {
								text: intl.formatMessage({
									id: "channel.type.all.title",
								}),
								status: "Default",
							},
							translation: {
								text: intl.formatMessage({
									id: "channel.type.translation.title",
								}),
								status: "Success",
							},
							nissaya: {
								text: intl.formatMessage({
									id: "channel.type.nissaya.title",
								}),
								status: "Processing",
							},
							commentary: {
								text: intl.formatMessage({
									id: "channel.type.commentary.title",
								}),
								status: "Default",
							},
							original: {
								text: intl.formatMessage({
									id: "channel.type.original.title",
								}),
								status: "Default",
							},
							general: {
								text: intl.formatMessage({
									id: "channel.type.general.title",
								}),
								status: "Default",
							},
						},
					},
					{
						title: intl.formatMessage({
							id: "forms.fields.publicity.label",
						}),
						dataIndex: "publicity",
						key: "publicity",
						width: 100,
						search: false,
						filters: true,
						onFilter: true,
						valueEnum: {
							all: {
								text: intl.formatMessage({
									id: "tables.publicity.all",
								}),
								status: "Default",
							},
							0: {
								text: intl.formatMessage({
									id: "tables.publicity.disable",
								}),
								status: "Default",
							},
							10: {
								text: intl.formatMessage({
									id: "tables.publicity.private",
								}),
								status: "Processing",
							},
							20: {
								text: intl.formatMessage({
									id: "tables.publicity.public.bylink",
								}),
								status: "Processing",
							},
							30: {
								text: intl.formatMessage({
									id: "tables.publicity.public",
								}),
								status: "Success",
							},
							40: {
								text: intl.formatMessage({
									id: "tables.publicity.public.edit",
								}),
								status: "Success",
							},
						},
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
						//sorter: (a, b) => a.createdAt - b.createdAt,
					},
					{
						title: intl.formatMessage({ id: "buttons.option" }),
						key: "option",
						width: 120,
						valueType: "option",
						render: (text, row, index, action) => [
							<Dropdown.Button
								key={index}
								type="link"
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
							{intl.formatMessage({ id: "buttons.selected" })}
							{selectedRowKeys.length}
							<Button
								type="link"
								style={{ marginInlineStart: 8 }}
								onClick={onCleanSelected}
							>
								{intl.formatMessage({ id: "buttons.unselect" })}
							</Button>
						</span>
					</Space>
				)}
				tableAlertOptionRender={() => {
					return (
						<Space size={16}>
							<Button type="link">
								{intl.formatMessage({
									id: "buttons.delete.all",
								})}
							</Button>
						</Space>
					);
				}}
				request={async (params = {}, sorter, filter) => {
					// TODO
					console.log(params, sorter, filter);

					const res: IApiResponseChannelList = await get(
						`/v2/channel?view=studio`
					);
					const items: IItem[] = res.data.rows.map((item, id) => {
						return {
							id: id,
							uid: item.uid,
							title: item.name,
							summary: item.summary,
							type: item.type,
							publicity: item.status,
							createdAt: item.created_at,
						};
					});
					return {
						total: res.data.count,
						succcess: true,
						data: items,
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
						content={channelCreate}
						title="new channel"
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
