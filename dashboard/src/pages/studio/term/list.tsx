import { useParams } from "react-router-dom";
import { ProTable } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import {
	Button,
	Layout,
	Space,
	Table,
	Popover,
	Dropdown,
	Menu,
	MenuProps,
} from "antd";
import { PlusOutlined, SearchOutlined } from "@ant-design/icons";

import TermCreate from "../../../components/studio/term/TermCreate";
import { ITermListResponse } from "../../../components/api/Term";
import { get } from "../../../request";

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
	sn: number;
	id: number;
	word: string;
	tag: string;
	channel: string;
	meaning: string;
	meaning2: string;
	note: string;
	createdAt: number;
}

const Widget = () => {
	const intl = useIntl();
	const { studioname } = useParams();

	return (
		<>
			<Layout>{studioname}</Layout>
			<ProTable<IItem>
				columns={[
					{
						title: intl.formatMessage({
							id: "term.fields.sn.label",
						}),
						dataIndex: "sn",
						key: "sn",
						width: 30,
						search: false,
					},
					{
						title: intl.formatMessage({
							id: "term.fields.word.label",
						}),
						dataIndex: "word",
						key: "word",
						tip: "单词过长会自动收缩",
						ellipsis: true,
						formItemProps: {
							rules: [
								{
									required: true,
									message: "此项为必填项",
								},
							],
						},
					},
					{
						title: intl.formatMessage({
							id: "term.fields.description.label",
						}),
						dataIndex: "tag",
						key: "description",
						search: false,
					},
					{
						title: intl.formatMessage({
							id: "term.fields.channel.label",
						}),
						dataIndex: "channel",
						valueType: "select",
						valueEnum: {
							all: { text: "全部" },
							1: { text: "中文" },
							2: { text: "中文草稿" },
							3: { text: "英文" },
							4: { text: "英文草稿" },
							5: { text: "Visuddhinanda" },
						},
					},
					{
						title: intl.formatMessage({
							id: "term.fields.meaning.label",
						}),
						dataIndex: "meaning",
						key: "meaning",
					},
					{
						title: intl.formatMessage({
							id: "term.fields.meaning2.label",
						}),
						dataIndex: "meaning2",
						key: "meaning2",
						tip: "意思过长会自动收缩",
						ellipsis: true,
					},
					{
						title: intl.formatMessage({
							id: "term.fields.note.label",
						}),
						dataIndex: "note",
						key: "note",
						search: false,
						tip: "注释过长会自动收缩",
						ellipsis: true,
					},
					{
						title: intl.formatMessage({
							id: "forms.fields.created-at.label",
						}),
						key: "created-at",
						width: 200,
						search: false,
						dataIndex: "createdAt",
						valueType: "date",
						sorter: (a, b) => a.createdAt - b.createdAt,
					},
					{
						title: intl.formatMessage({ id: "buttons.option" }),
						key: "option",
						width: 120,
						valueType: "option",
						render: (text, row, index, action) => {
							return [
								<Dropdown.Button
									key={index}
									type="link"
									overlay={menu}
									onClick={() => {
										//setWordId(row.wordId);
										//setDrawerTitle(row.word);
										//setIsEditOpen(true);
									}}
								>
									{intl.formatMessage({
										id: "buttons.edit",
									})}
								</Dropdown.Button>,
							];
						},
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
							<Button type="link">批量删除</Button>
							<Button type="link">导出数据</Button>
						</Space>
					);
				}}
				request={async (params = {}, sorter, filter) => {
					// TODO
					console.log(params, sorter, filter);
					const offset =
						((params.current ? params.current : 1) - 1) *
						(params.pageSize ? params.pageSize : 20);
					let url = `/v2/terms?view=studio&name=${studioname}&limit=${params.pageSize}&offset=${offset}`;
					if (typeof params.keyword !== "undefined") {
						url +=
							"&search=" + (params.keyword ? params.keyword : "");
					}
					console.log(url);
					const res = await get<ITermListResponse>(url);

					const items: IItem[] = res.data.rows.map((item, id) => {
						const date = new Date(item.updated_at);
						const id2 =
							((params.current || 1) - 1) *
								(params.pageSize || 20) +
							id +
							1;
						return {
							sn: id2,
							id: item.id,
							word: item.word,
							tag: item.tag,
							channel: item.channal,
							meaning: item.meaning,
							meaning2: item.other_meaning,
							note: item.note,
							createdAt: date.getTime(),
						};
					});
					return {
						total: res.data.count,
						success: true,
						data: items,
					};
				}}
				rowKey="id"
				//bordered
				pagination={{
					showQuickJumper: true,
					showSizeChanger: true,
				}}
				toolBarRender={() => [
					<Popover
						content={<TermCreate studio={studioname} />}
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
				search={false}
				options={{
					search: true,
				}}
				dateFormatter="string"
			/>
		</>
	);
};

export default Widget;
