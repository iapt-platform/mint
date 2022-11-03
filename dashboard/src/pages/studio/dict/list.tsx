import { useParams } from "react-router-dom";
import { ProTable } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { Link } from "react-router-dom";
import {
	Button,
	Layout,
	Space,
	Table,
	Dropdown,
	MenuProps,
	Menu,
	Drawer,
} from "antd";
import { PlusOutlined, SearchOutlined } from "@ant-design/icons";

import DictCreate from "../../../components/studio/dict/DictCreate";
import { IApiResponseDictList } from "../../../components/api/Dict";
import { get } from "../../../request";
import { useState } from "react";

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
	word: string;
	type: string;
	grammar: string;
	parent: string;
	meaning: string;
	note: string;
	factors: string;
	createdAt: number;
}

const valueEnum = {
	0: "n",
	1: "ti",
	2: "v",
	3: "ind",
};

const Widget = () => {
	const intl = useIntl();
	const { studioname } = useParams();
	const [isModalOpen, setIsModalOpen] = useState(false);
	const dictCreate = <DictCreate studio={studioname ? studioname : ""} />;

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
						width: 80,
						search: false,
					},
					{
						title: intl.formatMessage({
							id: "dict.fields.word.label",
						}),
						dataIndex: "word",
						key: "word",
						tip: "单词过长会自动收缩",
						ellipsis: true,
					},
					{
						title: intl.formatMessage({
							id: "dict.fields.type.label",
						}),
						dataIndex: "type",
						key: "type",
						search: false,
						filters: true,
						onFilter: true,
						valueEnum: {
							all: { text: "全部", status: "Default" },
							n: { text: "名词", status: "Default" },
							ti: { text: "三性", status: "Processing" },
							v: { text: "动词", status: "Success" },
							ind: { text: "不变词", status: "Success" },
						},
					},
					{
						title: intl.formatMessage({
							id: "dict.fields.grammar.label",
						}),
						dataIndex: "grammar",
						key: "grammar",
						search: false,
					},
					{
						title: intl.formatMessage({
							id: "dict.fields.parent.label",
						}),
						dataIndex: "parent",
						key: "parent",
					},
					{
						title: intl.formatMessage({
							id: "dict.fields.meaning.label",
						}),
						dataIndex: "meaning",
						key: "meaning",
						tip: "意思过长会自动收缩",
						ellipsis: true,
					},
					{
						title: intl.formatMessage({
							id: "dict.fields.note.label",
						}),
						dataIndex: "note",
						key: "note",
						search: false,
						tip: "注释过长会自动收缩",
						ellipsis: true,
					},
					{
						title: intl.formatMessage({
							id: "dict.fields.factors.label",
						}),
						dataIndex: "factors",
						key: "factors",
						search: false,
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
								>
									<Link
										to={`/studio/${studioname}/dict/${row.id}/edit`}
									>
										{intl.formatMessage({
											id: "buttons.edit",
										})}
									</Link>
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
					let url = `/v2/userdict?view=studio&name=${studioname}&limit=${params.pageSize}&offset=${offset}`;
					if (typeof params.keyword !== "undefined") {
						url +=
							"&search=" + (params.keyword ? params.keyword : "");
					}
					console.log(url);
					const res: IApiResponseDictList = await get(url);

					const items: IItem[] = res.data.rows.map((item, id) => {
						const date = new Date(item.updated_at);
						const id2 =
							((params.current || 1) - 1) *
								(params.pageSize || 20) +
							id +
							1;
						return {
							id: id2,
							word: item.word,
							type: item.type,
							grammar: item.grammar,
							parent: item.parent,
							meaning: item.mean,
							note: item.note,
							factors: item.factors,
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
				bordered
				pagination={{
					showQuickJumper: true,
					showSizeChanger: true,
				}}
				search={false}
				options={{
					search: true,
				}}
				headerTitle=""
				toolBarRender={() => [
					<Button
						key="button"
						icon={<PlusOutlined />}
						type="primary"
						onClick={() => {
							setIsModalOpen(true);
						}}
					>
						{intl.formatMessage({ id: "buttons.create" })}
					</Button>,
				]}
			/>

			<Drawer
				title="new word"
				placement="right"
				open={isModalOpen}
				onClose={() => {
					setIsModalOpen(false);
				}}
				size="large"
				style={{ minWidth: 736, maxWidth: "100%" }}
				contentWrapperStyle={{ overflowY: "auto" }}
				footer={null}
			>
				{dictCreate}
			</Drawer>
		</>
	);
};

export default Widget;
