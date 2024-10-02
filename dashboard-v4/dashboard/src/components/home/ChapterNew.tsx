import { Col, Row } from "antd";
import { Link } from "react-router-dom";
import img_book from "../../assets/library/images/books.svg";
import ChapterNewList from "./ChapterNewList";
import { useIntl } from "react-intl";

const ChapterNewWidget = () => {
  const intl = useIntl();
  return (
    <Row
      style={{
        backgroundColor: "#e0e0e0",
        minHeight: 200,
      }}
    >
      <Col flex="auto"></Col>
      <Col
        flex="960px"
        style={{
          display: "flex",
          backgroundImage: `url(${img_book})`,
          backgroundRepeat: "no-repeat",
        }}
      >
        <div style={{ flex: 4, margin: "2em" }}>
          <span style={{ fontSize: "250%", fontWeight: 700 }}>
            {intl.formatMessage({ id: "columns.library.community.title" })}
          </span>
          <span style={{ position: "absolute", right: 30, bottom: 0 }}>
            <Link to="community/list">
              {intl.formatMessage({ id: "buttons.more" })}
            </Link>
          </span>
        </div>
        <div style={{ flex: 9 }}>
          <ChapterNewList />
        </div>
      </Col>
      <Col flex="auto"></Col>
    </Row>
  );
};

export default ChapterNewWidget;
