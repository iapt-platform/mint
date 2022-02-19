import {Link,history} from 'umi';
import {Button} from 'antd';

import {WidgetCourseInfo,WidgetCourseContent} from '@/components/demo'

export default ({match}) => {

  return (
    <div>
      <Link to="/demo">网站主页</Link>
      &nbsp;
      <Link to="/demo/day-2">课程主页</Link>
      <h1>课程名称 {match.params.id}</h1>
      <Button type="primary" onClick={()=>history.push('/demo/course/'+match.params.id+'/edit')}>编辑</Button>

      <WidgetCourseInfo title="转法轮经" course_id={match.params.id} teacher_name="悟贡西亚多" />
      <WidgetCourseContent text="转法轮经内容 转法轮经内容 转法轮经内容 转法轮经内容"  />
    </div>
  )
}
