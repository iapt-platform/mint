import {Link} from 'umi';

export default ({match}) => {
  return (
    <div>
      <h1>welcome to  {match.params.username} zone</h1>
      <Link to="/demo">Go home</Link>
    </div>
  )
}
