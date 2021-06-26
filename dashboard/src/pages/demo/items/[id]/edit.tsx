import {Link} from 'umi';

import {Widget1, Widget2} from '@/components/demo'

export default ({match}) => {
  return (
    <div>
      <h1>Edit item page by id {match.params.id}</h1>
      <Link to="/demo">Go home</Link>
      <Widget1 message={"edit page " + match.params.id}/>
      <Widget2/>
    </div>
  );
}
