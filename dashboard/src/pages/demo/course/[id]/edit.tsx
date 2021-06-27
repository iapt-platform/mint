import {Link} from 'umi';
import {Button} from 'antd';

import {WidgetCourseInfo,WidgetWikiPaliArticleEdit} from '@/components/demo'

export default ({match}) => {
  return (
    <div>
      <h1>Edit item page by id {match.params.id}</h1>
      <Link to="/demo">网站主页</Link>
      &nbsp;
      <Link to="/demo/day-2">课程主页</Link>
      <h1>课程名称 {match.params.id}</h1>
      <Button type="primary">保存</Button>
      <Button >取消</Button>
      
      <WidgetCourseInfo title="转法轮经" course_id={match.params.id} teacher_name="悟贡西亚多" />
      <WidgetWikiPaliArticleEdit text="转法轮经内容 转法轮经内容 转法轮经内容 转法轮经内容"  />

    </div>
  );
}
