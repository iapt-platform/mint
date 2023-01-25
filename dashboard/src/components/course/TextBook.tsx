import { Col, Row } from "antd";
import AnthologyDetail from "../article/AnthologyDetail";

interface IWidget {
  anthologyId?: string;
}
const Widget = ({ anthologyId }: IWidget) => {
  console.log("anthologyId", anthologyId);
  return (
    <div style={{ backgroundColor: "#f5f5f5" }}>
      <Row>
        <Col flex="auto"></Col>
        <Col flex="1260px">
          <AnthologyDetail aid={anthologyId} />
        </Col>
        <Col flex="auto"></Col>
      </Row>
    </div>
  );
};

export default Widget;
