import { Col, Row } from "antd";
import { useNavigate } from "react-router-dom";
import AnthologyDetail from "../article/AnthologyDetail";
import { fullUrl } from "../../utils";

interface IWidget {
  anthologyId?: string;
  courseId?: string;
}
const TextBookWidget = ({ anthologyId, courseId }: IWidget) => {
  const navigate = useNavigate();

  console.log("anthologyId", anthologyId);
  return (
    <div style={{ backgroundColor: "#f5f5f5" }}>
      <Row>
        <Col flex="auto"></Col>
        <Col flex="960px">
          <AnthologyDetail
            aid={anthologyId}
            onArticleClick={(
              anthologyId: string,
              articleId: string,
              target: string
            ) => {
              const url = `/article/textbook/${articleId}?mode=read&course=${courseId}`;
              if (target === "_blank") {
                window.open(fullUrl(url), "_blank");
              } else {
                navigate(url);
              }
            }}
          />
        </Col>
        <Col flex="auto"></Col>
      </Row>
    </div>
  );
};

export default TextBookWidget;
