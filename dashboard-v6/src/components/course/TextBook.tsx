import { Col, Row } from "antd";
import { useNavigate } from "react-router";

import { fullUrl } from "../../utils";
import AnthologyDetail from "../anthology/AnthologyDetail";
import type { TTarget } from "../../types";

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
            onArticleClick={(_, articleId: string, target?: TTarget) => {
              const url = `/workspace/course/${courseId}/textbook/${articleId}?mode=read&course=${courseId}`;
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
