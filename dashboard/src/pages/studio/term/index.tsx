import { useParams } from "react-router-dom";
import { ProTable } from "@ant-design/pro-components";
import { useIntl } from "react-intl";
import { Link } from "react-router-dom";
import { Button, Layout, Space, Table } from "antd";
import { PlusOutlined } from "@ant-design/icons";

import HeadBar from "../../../components/studio/HeadBar";
import LeftSider from "../../../components/studio/LeftSider";
import Footer from "../../../components/studio/Footer";

const { Content } = Layout;

interface IItem {
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
		<Layout>
			<HeadBar />
			<Layout>
				<LeftSider selectedKeys="term" />
				<Content>
					<Layout>{studioname}</Layout>
					<ProTable<IItem>
						columns={[
							{
								title: intl.formatMessage({ id: "term.fields.sn.label" }),
								dataIndex: "id",
								key: "id",
								width: 80,
								search: false,
							},
							{
								title: intl.formatMessage({ id: "term.fields.word.label" }),
								dataIndex: "word",
								key: "word",
								render: (_) => <Link to="">{_}</Link>,
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
								title: intl.formatMessage({ id: "term.fields.description.label" }),
								dataIndex: "tag",
								key: "description",
								search: false,
							},
							{
								title: intl.formatMessage({ id: "term.fields.channel.label" }),
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
								title: intl.formatMessage({ id: "term.fields.meaning.label" }),
								dataIndex: "meaning",
								key: "meaning",
							},
							{
								title: intl.formatMessage({ id: "term.fields.meaning2.label" }),
								dataIndex: "meaning2",
								key: "meaning2",
								tip: "意思过长会自动收缩",
								ellipsis: true,
							},
							{
								title: intl.formatMessage({ id: "term.fields.note.label" }),
								dataIndex: "note",
								key: "note",
								search: false,
								tip: "注释过长会自动收缩",
								ellipsis: true,
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
										tag: "",
										channel: "2",
										meaning: `parent ${id}`,
										meaning2: `meaning ${id}`,
										note: `note ${id}`,
										createdAt: Date.now() - Math.floor(Math.random() * 200000),
									};
									return it;
								}),
							};
						}}
						rowKey="id"
						//bordered
						pagination={{
							showQuickJumper: true,
							showSizeChanger: true,
						}}
						headerTitle={intl.formatMessage({ id: "dict" })}
						toolBarRender={() => [
							<Button key="button" icon={<PlusOutlined />} type="primary">
								新建
							</Button>,
						]}
						search={{
							filterType: "light",
						}}
						dateFormatter="string"
					/>
				</Content>
			</Layout>
			<Footer />
		</Layout>
	);
};

export default Widget;
