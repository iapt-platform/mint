import { useIntl } from "react-intl";
import { useState } from 'react';
import { ProList } from '@ant-design/pro-components';
import { Space, Tag, Button, Layout } from 'antd';
const {  Content } = Layout;

const defaultData = [
	{
	  id: '1',
	  name: '小僧善巧',
	  tag:[{title:"管理员",color:"success"}],
	  image:'https://gw.alipayobjects.com/zos/antfincdn/efFD%24IOql2/weixintupian_20170331104822.jpg',
	},
	{
	  id: '2',
	  name: '无语',
	  tag:[{title:"管理员",color:"success"}],
	  image:'https://gw.alipayobjects.com/zos/antfincdn/efFD%24IOql2/weixintupian_20170331104822.jpg',
	},
	{
	  id: '3',
	  name: '慧欣',
	  tag:[],
	  image:'https://gw.alipayobjects.com/zos/antfincdn/efFD%24IOql2/weixintupian_20170331104822.jpg',
	},
	{
	  id: '4',
	  name: '谭博文',
	  tag:[],
	  image:'https://gw.alipayobjects.com/zos/antfincdn/efFD%24IOql2/weixintupian_20170331104822.jpg',
	},
	{
		id: '4',
		name: '豆沙猫',
		tag:[],
		image:'https://gw.alipayobjects.com/zos/antfincdn/efFD%24IOql2/weixintupian_20170331104822.jpg',
	},  
	{
		id: '4',
		name: 'visuddhinanda',
		tag:[],
		image:'https://gw.alipayobjects.com/zos/antfincdn/efFD%24IOql2/weixintupian_20170331104822.jpg',
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
					headerTitle={intl.formatMessage({ id: "group.member" })}
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
				/>			
			</Content>

  );
};

export default Widget;
