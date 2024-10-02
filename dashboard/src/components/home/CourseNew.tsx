import { Col, Row } from "antd";
import { Link } from "react-router-dom";
import img_book from "../../assets/library/images/teachers.svg";
import CourseNewList from "./CourseNewList";
import { useIntl } from "react-intl";

const CourseNewWidget = () => {
  const intl = useIntl(); //i18n
  return (
    <Row
      style={{
        backgroundColor: "#313131",
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
          backgroundPositionX: "right",
        }}
      >
        <div style={{ flex: 9 }}>
          <CourseNewList />
        </div>
        <div style={{ flex: 4, margin: "2em" }}>
          <span style={{ fontSize: "250%", fontWeight: 700, color: "white" }}>
            课程
          </span>
          <span style={{ position: "absolute", right: 30, bottom: 0 }}>
            <Link to="course/list">
              {intl.formatMessage({ id: "buttons.more" })}
            </Link>
          </span>
        </div>
      </Col>
      <Col flex="auto"></Col>
    </Row>
  );
};

export default CourseNewWidget;
