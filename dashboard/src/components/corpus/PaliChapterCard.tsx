import { Row, Col } from "antd";
import { Typography } from "antd";
import { API_HOST } from "../../request";

import TocPath from "./TocPath";

const { Title, Link } = Typography;

export interface IPaliChapterData {
  Title: string;
  PaliTitle: string;
  level: number;
  Path: string;
  Book: number;
  Paragraph: number;
}

interface IWidget {
  data: IPaliChapterData;
  onTitleClick?: Function;
}

const Widget = ({ data, onTitleClick }: IWidget) => {
  const path = JSON.parse(data.Path);

  return (
    <>
      <Row>
        <Col span={3}></Col>
        <Col span={21}>
          <Row>
            <Col span={16}>
              <Row>
                <Col>
                  <Title
                    level={5}
                    onClick={(e) => {
                      if (typeof onTitleClick !== "undefined") {
                        onTitleClick(e);
                      }
                    }}
                  >
                    <Link>{data.Title}</Link>
                  </Title>
                </Col>
              </Row>
              <Row>
                <Col>{data.PaliTitle}</Col>
              </Row>
              <Row>
                <Col>
                  <TocPath data={path} />
                </Col>
              </Row>
            </Col>
            <Col span={8}>
              <img
                src={`${API_HOST}/storage/images/chapter_dynamic/${data.Book}/${data.Paragraph}/globle.svg`}
                width={200}
                alt="章节动态"
              />
            </Col>
          </Row>
          <Row>
            <Col></Col>
          </Row>
          <Row>
            <Col span={16}></Col>
          </Row>
        </Col>
      </Row>
    </>
  );
};

export default Widget;
