import MDEditor from "@uiw/react-md-editor";
import ReactMarkdown from "react-markdown";
import { Card } from "antd";

interface IProps {
  body: string;
}

const Widget = ({ body }: IProps) => (
  <>
    <Card title="Markdown Viewer 1">
      <MDEditor.Markdown source={body} />
    </Card>

    <Card title="Markdown Viewer 2">
      <ReactMarkdown>{body}</ReactMarkdown>
    </Card>
  </>
);

export default Widget;
