import { useIntl } from "react-intl";
import { useState } from 'react';
import { ProList } from '@ant-design/pro-components';
import { Space, Tag, Button, Layout } from 'antd';
const {  Content } = Layout;

const defaultData = [
	{
	  id: '1',
	  name: '庄春江工作站',
	  tag:[{title:"可编辑",color:"success"}],
	  image:
		'https://gw.alipayobjects.com/zos/antfincdn/efFD%24IOql2/weixintupian_20170331104822.jpg',
		description: 'IAPT|2022-1-3',
	},
	{
	  id: '2',
	  name: '元亨寺·CBETA',
	  tag:[{title:"可编辑",color:"success"}],
	  image:
		'https://gw.alipayobjects.com/zos/antfincdn/efFD%24IOql2/weixintupian_20170331104822.jpg',
		description: '我是一条测试的描述',
	},
	{
	  id: '3',
	  name: '叶均居士',
	  tag:[{title:"只读",color:"default"}],
	  image:
		'https://gw.alipayobjects.com/zos/antfincdn/efFD%24IOql2/weixintupian_20170331104822.jpg',
		description: '我是一条测试的描述',
	},
	{
	  id: '4',
	  name: '玛欣德尊者',
	  tag:[{title:"只读",color:"default"}],
	  image:
		'https://gw.alipayobjects.com/zos/antfincdn/efFD%24IOql2/weixintupian_20170331104822.jpg',
		description: '我是一条测试的描述',
	},
  ];
  type DataItem = typeof defaultData[number];
  type IWidgetGroupFile ={
	groupid?: string
}
  const Widget = ({groupid = ''}: IWidgetGroupFile) => {
	const intl = useIntl();//i18n
	const [dataSource, setDataSource] = useState<DataItem[]>(defaultData);

  return (
			<Content>

				<Space>{groupid}</Space>
				<ProList<DataItem>
					rowKey="id"
					headerTitle={intl.formatMessage({ id: "group.files" })}
					dataSource={dataSource}
					showActions="hover"
					onDataSourceChange={setDataSource}
					metas={{
						title: {
						dataIndex: 'name',
						},
						avatar: {
						dataIndex: 'image',
						editable: false,
						},
						description: {
						dataIndex: 'description',
						},
						content: {
							dataIndex: 'content',
							editable: false,
						},
						subTitle: {
						render: (text, row, index, action) => {
							const showtag = row.tag.map((item,key) => {
								return <Tag color={item.color}>{item.title}</Tag>
							});
							return (
							<Space size={0}>
								{showtag}
							</Space>
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
							删除
							</Button>,
						],
						},
					}}
					pagination={{
						showQuickJumper: true,
						showSizeChanger: true,
					  }}
				/>			
			</Content>

  );
};

export default Widget;
