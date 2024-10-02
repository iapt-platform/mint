import { useNavigate, useParams } from "react-router-dom";
import { Col, Row } from "antd";

import AnthologyDetail from "../../../components/article/AnthologyDetail";
import { fullUrl } from "../../../utils";

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
            aid={id}
            onArticleClick={(
              anthologyId: string,
              articleId: string,
              target: string
            ) => {
              let url = `/article/article/${articleId}?mode=read&anthology=${anthologyId}`;
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
    </>
  );
};

export default Widget;
