import { Col, Row } from "antd";
import img_book from "../../assets/library/images/books.svg";
import ChapterNewList from "./ChapterNewList";

const Widget = () => {
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
        <div style={{ flex: 4 }}>
          <h3 style={{ fontSize: "250%" }}>圣典</h3>
          <span style={{ position: "absolute", right: 30, bottom: 0 }}>
            <a href="../collect">更多</a>
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

export default Widget;
