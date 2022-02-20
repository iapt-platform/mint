import {Link} from 'umi';
import { Alert } from 'antd';
import { Input } from 'antd';
import { WarningOutlined,LoadingOutlined  } from '@ant-design/icons';

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

type IWidgetCourseInfo ={
  title: string,
  course_id:number,
  teacher_name:string,
}

export const WidgetCourseInfo = (course: IWidgetCourseInfo) => {
  return (
    <div>
      课程名称
      <Alert message={course.title} type="success" />
      课程id：{course.course_id}<br />
      授课教师：{course.teacher_name}<br />
    </div>
  );
}

type IWidgetArticle ={
  text: string
}
export const WidgetCourseContent = (course: IWidgetArticle) => {
  return (
    <div>
      <h2>课程内容</h2>
      <WidgetWikiPaliArticle text={course.text} />
    </div>
  );
}


export const WidgetWikiPaliArticle = (content: IWidgetArticle) => {
  return (
    <div>
      <p>wikipali article content</p>
      {content.text}
    </div>
  );
}

export const WidgetWikiPaliArticleEdit = (content: IWidgetArticle) => {
  const onChange = e => {
    console.log('Change:', e.target.value);
  };

  const { TextArea } = Input;
  return (
    <div>
      <p>wikipali article content edit</p>
      <TextArea autoSize showCount maxLength={500} defaultValue={content.text} onChange={onChange} />
    </div>
  );
}

type IWidgetCommitMessage ={
  message: string,
  successful:boolean,
  time:number,
}

export const WidgetCommitNofifiction = (message: IWidgetCommitMessage) => {
        if(message.successful){
          return (
            <span>
              <LoadingOutlined />{message.time}{message.message}
            </span>
          );          
        }
        else{
          return (
            <span>
              <WarningOutlined />{message.time}{message.message}
            </span>
          );
        } 

}
