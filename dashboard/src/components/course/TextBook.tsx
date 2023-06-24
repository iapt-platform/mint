import { Col, Row } from "antd";
import { useNavigate } from "react-router-dom";
import AnthologyDetail from "../article/AnthologyDetail";

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
            onArticleSelect={(keys: string[]) => {
              navigate(`/article/textbook/${courseId}_${keys[0]}/read`);
            }}
          />
        </Col>
        <Col flex="auto"></Col>
      </Row>
    </div>
  );
};

export default TextBookWidget;
