import {Link, history} from 'umi';
import {Button} from 'antd';

type IProps = {

}

export default ({}: IProps) => {
  return (
    <div>
      <h1>Demo home page</h1>
      <div>
        <Link to="/demo/items">List items</Link>
        &nbsp;
        <Link to="/demo/items/1/show">Show items 1</Link>
        &nbsp;
        <Link to="/demo/items/2/show">Show items 2</Link>
        &nbsp;
        <Button onClick={()=>history.push('/demo/items/333/edit')}>Edit items 333</Button>
      </div>
    </div>
  );
}
