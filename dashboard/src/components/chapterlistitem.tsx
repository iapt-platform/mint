import {Link} from 'umi';
import { Breadcrumb} from "antd";
import { WarningOutlined,LoadingOutlined  } from '@ant-design/icons';
import { Col, Row } from 'antd';

type IWidgetChapterListItem ={
	title: string,
	path:string,
	summary:string,
	url:string,
	like:number,
	hit:number,
	favorite:number,
	watch:number,
	created_at:string,
	updated_at:string,
  }
  
  export const ChapterListItem = (item: IWidgetChapterListItem) => {
	let routes = new Array;
	for (const iterator of JSON.parse(item.path)) {
		routes.push({
			path:'/app/article/?view=chapter&book='+iterator.book+'&par='+iterator.paragraph,
			breadcrumbName:iterator.title,
		}) ;
	}
	function itemRender(route,params,routes,paths){
		return (
			<Link to={route.path}>{route.breadcrumbName}</Link>
		)
	}
	return (
	  <div>
		<Row>
			<Col xs={24} xl={14}><Link to={item.url}>{item.title}</Link></Col>
			<Col xs={0} xl={10}>点赞（1）</Col>
		</Row>
		<Row>
			<Col xs={24} xl={0}>点赞（1）</Col>
			<Col xs={0} xl={0}></Col>
		</Row>
		<Row>
			<Col xs={24} xl={0}><Breadcrumb itemRender={itemRender} routes={routes} /></Col>
			<Col xs={0} xl={0}></Col>
		</Row>
		<div>{item.summary}</div>
	  </div>
	);
  }