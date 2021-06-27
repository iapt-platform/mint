import {Link} from 'umi';
import {Modal, Button ,Space} from 'antd';
import { ExclamationCircleOutlined } from '@ant-design/icons';


import {WidgetCourseInfo,WidgetWikiPaliArticleEdit} from '@/components/demo'


export default ({match}) => {
  function confirm() {
    Modal.confirm({
      title: '存盘',
      icon: <ExclamationCircleOutlined />,
      content: '保存所做的修改吗？',
      okText: '确认',
      cancelText: '取消',
    });
  }

  return (
    <div>
      <h1>Edit item page by id {match.params.id}</h1>
      <Link to="/demo">网站主页</Link>
      &nbsp;
      <Link to="/demo/day-2">课程主页</Link>
      <h1>课程名称 {match.params.id}</h1>
      <Space>
        <Button type="primary" onClick={confirm}>保存</Button>
        <Button >还原</Button>        
      </Space>
      
      <WidgetCourseInfo title="转法轮经" course_id={match.params.id} teacher_name="悟贡西亚多" />
      <WidgetWikiPaliArticleEdit text="转法轮经内容 转法轮经内容 转法轮经内容 转法轮经内容"  />

    </div>
  );
}
