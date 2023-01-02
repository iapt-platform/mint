//课程详情页面
import { Link } from "react-router-dom";
import { useParams } from "react-router-dom";
import { Layout, Col, Row,Divider } from "antd";
import CourseShow from "../../../components/library/course/CourseShow";
import CourseIntro from "../../../components/library/course/CourseIntro";
import TocTree from "../../../components/article/TocTree";
import { ListNodeData } from "../../../components/article/EditableTree";
import ReactMarkdown from 'react-markdown'
import rehypeRaw from 'rehype-raw'
const { Content, Header } = Layout;

let arrTocTree: ListNodeData[] = [];
let  i = 0;
do {
  ++i
  arrTocTree.push({
    key: i.toString(),
    title: `课程 ${i}`,
    level: 1,
  });
   }while(i<10)  // 在循环的尾部检查条件


   let markdown='# 这是标题\n' +
   '[ **M** ] arkdown + E [ **ditor** ] = **Mditor**  \n' +
   '> Mditor 是一个简洁、易于集成、方便扩展、期望舒服的编写 markdown 的编辑器，仅此而已... \n\n' +
  '**这是加粗的文字**\n\n' +
   '*这是倾斜的文字*`\n\n' +
   '***这是斜体加粗的文字***\n\n' +
   '~~这是加删除线的文字~~ \n\n'+
   '\`console.log(Hello World)\` \n\n'+
   '```const a=2; ```'

const Widget = () => {
  // TODO
  const { courseid } = useParams(); //url 参数
  
  return (
    <Layout>


    <Content>
      <Row>
          <Col flex="auto"></Col>
          <Col flex="1760px">
    <div>
      <div>
      <CourseShow />
      <Divider />   
      <ReactMarkdown rehypePlugins={[rehypeRaw]} children={markdown} />
      <CourseIntro />
      <Divider />   
      <TocTree treeData={arrTocTree} />
        </div>

    </div>
    </Col>
    </Row>
      </Content>
    </Layout>
  );
};

export default Widget;


/*
  return (
    <div>
      <div>课程{courseid} 详情</div>
      <div>
        <Link to="/course/lesson/12345">lesson 1</Link>
        <Link to="/course/lesson/23456">lesson 2</Link>
      </div>
    </div>
  );
*/