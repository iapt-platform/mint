import { useNavigate, useParams } from "react-router-dom";
import { Col, Row } from "antd";

import AnthologyDetail from "../../../components/article/AnthologyDetail";

const Widget = () => {
  // TODO
  const { id } = useParams(); //url 参数
  const navigate = useNavigate();

  const pageMaxWidth = "960px";
  return (
    <>
      <Row>
        <Col flex="auto"></Col>
        <Col flex={pageMaxWidth}>
          <AnthologyDetail
            onArticleSelect={(anthologyId: string, keys: string[]) => {
              if (keys[0]) {
                navigate(
                  `/article/article/${keys[0]}?mode=read&anthology=${anthologyId}`
                );
              }
            }}
            aid={id}
          />
        </Col>
        <Col flex="auto"></Col>
      </Row>
    </>
  );
};

export default Widget;
