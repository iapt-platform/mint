//讲页面
import { Link } from "react-router-dom";
import { useParams } from "react-router-dom";
import { Layout, Col, Row, Divider } from "antd";
import CourseShow from "../../../components/library/course/CourseShow";
import CourseIntro from "../../../components/library/course/CourseIntro";
import TocTree from "../../../components/article/TocTree";
import { ListNodeData } from "../../../components/article/EditableTree";
import ReactMarkdown from "react-markdown";
import rehypeRaw from "rehype-raw";
import { marked } from "marked";
const { Content, Header } = Layout;

let arrTocTree: ListNodeData[] = [];
let i = 0;
do {
  ++i;
  arrTocTree.push({
    key: i.toString(),
    title: `课程 ${i}`,
    level: 1,
  });
} while (i < 10); // 在循环的尾部检查条件

let markdown =
  "# 这是标题\n" +
  "[ **M** ] arkdown + E [ **ditor** ] = **Mditor**  \n" +
  "> Mditor 是一个简洁、易于集成、方便扩展、期望舒服的编写 markdown 的编辑器，仅此而已... \n\n" +
  "**这是加粗的文字**\n\n" +
  "*这是倾斜的文字*`\n\n" +
  "***这是斜体加粗的文字***\n\n" +
  "~~这是加删除线的文字~~ \n\n" +
  "\n\n" +
  "|表格头1|表格头2|表格头3| \n\n" +
  "|------|------|------| \n\n" +
  "| 文本 | 文本 | 文本 |\n\n" +
  "\n\n" +
  "```const a=2; ```";

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
                <h1 style={{ fontWeight: "bold", fontSize: 30 }}>课程7</h1>
                <Divider />
                <div
                  dangerouslySetInnerHTML={{
                    __html: marked.parse(markdown),
                  }}
                ></div>
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
      <p style={{ "fontWeight": 'bold', "fontSize": 15}}>时间安排： 2022/2/2 </p>
      <p style={{ "fontWeight": 'bold', "fontSize": 15}}>持续时间： 2小时 </p>
      <p style={{ "fontWeight": 'bold', "fontSize": 15}}>主讲人： <Link to="/course/lesson/12345">小僧善巧</Link> </p>
    
      <Divider /> 
        <h2 style={{ "fontWeight": 'bold', "fontSize": 20}}>直播预告</h2>
      <p style={{ "fontWeight": 'bold', "fontSize": 15}}>marked is not a function </p>
        <h2 style={{ "fontWeight": 'bold', "fontSize": 20}}>录播回放</h2>
      <p style={{ "fontWeight": 'bold', "fontSize": 15}}>marked is not a function </p>
      <h2 style={{ "fontWeight": 'bold', "fontSize": 20}}>内容</h2>
      <p style={{ "fontWeight": 'bold', "fontSize": 15}}>marked is not a function </p>
*/

/*
import { useParams } from "react-router-dom";

const Widget = () => {
  // TODO
  const { lessonid } = useParams(); //url 参数

  return (
    <div>
      <div>课 {lessonid} 详情</div>
      <div>
        主显示区
      </div>
    </div>
  );
};
*/
