import {Link} from 'umi';
import { Alert } from 'antd';

type IWidget1Props ={
  message: String
}

export const Widget1 = ({message}: IWidget1Props) => {
  return (
    <div>
      bla bla bla
      <Alert message={message} type="success" />
    </div>
  );
}

export const Widget2 = ({}) => {
  return (
    <div>
      bla bla bla
      <Alert message="Widget 2" type="error" />
    </div>
  );
}
