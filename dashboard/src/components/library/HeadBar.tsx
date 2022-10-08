import { Link } from "react-router-dom";
import { Layout } from 'antd';
import { useIntl } from "react-intl";
import type { MenuProps } from 'antd';
import { Menu } from 'antd';

const onClick: MenuProps['onClick'] = e => {
    console.log('click ', e);
  };

type IWidgetHeadBar ={
	selectedKeys?: string
}
const Widget = ({selectedKeys = ''}: IWidgetHeadBar) => {
	//Library head bar
	const intl = useIntl();//i18n
	// TODO
	const items: MenuProps['items'] = [
		{
		  label: (<Link to = "/community">{intl.formatMessage({ id: "columns.library.community.title" })}</Link>),
		  key: 'community',
		},
		{
			label: (<Link to = "/palicanon">{intl.formatMessage({ id: "columns.library.palicanon.title" })}</Link>),
			key: 'palicanon',
		},
		{
			label: (<Link to = "/course">{intl.formatMessage({ id: "columns.library.course.title" })}</Link>),
			key: 'course',
		},
		{
			label: (<Link to = "/dict">{intl.formatMessage({ id: "columns.library.dict.title" })}</Link>),
			key: 'dict',
		},
		{
			label: (<Link to = "/anthology">{intl.formatMessage({ id: "columns.library.anthology.title" })}</Link>),
			key: 'anthology',
		},		
		{
			label: (<a href = "https://asset-hk.wikipali.org/help/zh-Hans" target="_blank" rel="noreferrer">{intl.formatMessage({ id: "columns.library.help.title" })}</a>),
			key: 'help',
		},
	];
  return (
	<Layout>
		<Menu onClick={onClick} selectedKeys={[selectedKeys]} mode="horizontal" theme="dark" items={items} />
	</Layout>
  );
};

export default Widget;
