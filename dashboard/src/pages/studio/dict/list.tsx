import { useParams } from "react-router-dom";
import { ProTable } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { Link } from "react-router-dom";
import { Button, Layout, Space, Table, Popover } from "antd";
import { PlusOutlined } from "@ant-design/icons";

import DictCreate from "../../../components/studio/dict/DictCreate";

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

	const dictCreate = <DictCreate studio={studioname} />;

	return (
		<>
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
						title: intl.formatMessage({ id: "dict.fields.word.label" }),
						dataIndex: "word",
						key: "word",
						render: (_) => <Link to="">{_}</Link>,
						tip: "单词过长会自动收缩",
						ellipsis: true,
					},
					{
						title: intl.formatMessage({ id: "dict.fields.type.label" }),
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
						title: intl.formatMessage({ id: "dict.fields.grammar.label" }),
						dataIndex: "grammar",
						key: "grammar",
						search: false,
					},
					{
						title: intl.formatMessage({ id: "dict.fields.parent.label" }),
						dataIndex: "parent",
						key: "parent",
					},
					{
						title: intl.formatMessage({ id: "dict.fields.meaning.label" }),
						dataIndex: "meaning",
						key: "meaning",
						tip: "意思过长会自动收缩",
						ellipsis: true,
					},
					{
						title: intl.formatMessage({ id: "dict.fields.note.label" }),
						dataIndex: "note",
						key: "note",
						search: false,
						tip: "注释过长会自动收缩",
						ellipsis: true,
					},
					{
						title: intl.formatMessage({ id: "dict.fields.factors.label" }),
						dataIndex: "factors",
						key: "factors",
						search: false,
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
							<Button type="link">导出数据</Button>
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
								word: `word ${id}`,
								type: valueEnum[2],
								grammar: "阳-单-属",
								parent: `parent ${id}`,
								meaning: `meaning ${id}`,
								note: `note ${id}`,
								factors: `factors ${id}`,
								createdAt: Date.now() - Math.floor(Math.random() * 200000),
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
				headerTitle=""
				toolBarRender={() => [
					<Popover content={dictCreate} title="new channel" placement="bottomRight">
						<Button key="button" icon={<PlusOutlined />} type="primary">
							{intl.formatMessage({ id: "buttons.create" })}
						</Button>
					</Popover>,
				]}
			/>
		</>
	);
};

export default Widget;
