import {Link, history} from 'umi';
import {Button} from 'antd';

type IProps = {

}

export default ({}: IProps) => {
  return (
    <div>
      <h1>Wikipali Rebuild Day-2</h1>
      <div>
          <div><Link to="/demo/course">全部课程</Link></div>
          <div>最新课程</div>
         <ul>         
          <li><Link to="/demo/course/1/show">课程1</Link></li>
          <li><Link to="/demo/course/2/show">课程2</Link></li>
          <li><Link to="/demo/course/3/show">课程3</Link></li>
          <li><Link to="/demo/course/4/show">课程4</Link></li>
        </ul>
      </div>
    </div>
  );
}
