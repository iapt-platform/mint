import { Row, Col, Space } from "antd";
import { Typography } from "antd";
import { TinyLine } from "@ant-design/plots";
import TocPath from "./TocPath";

const { Title, Text, Link } = Typography;

export interface IPaliChapterData {
  Title: string;
  PaliTitle: string;
  level: number;
  Path: string;
  Book: number;
  Paragraph: number;
  chapterStrLen: number;
  paragraphCount: number;
  progressLine?: number[];
}

interface IWidget {
  data: IPaliChapterData;
  onTitleClick?: Function;
}

const PaliChapterCardWidget = ({ data, onTitleClick }: IWidget) => {
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
              {data.progressLine ? (
                <TinyLine
                  height={60}
                  width={200}
                  autoFit={false}
                  data={data.progressLine}
                  smooth={true}
                />
              ) : (
                <></>
              )}
            </Col>
          </Row>
          <Row>
            <Col>
              <Text type="secondary">
                <Space>
                  字符数{data.chapterStrLen} | 段落数{data.paragraphCount}
                </Space>
              </Text>
            </Col>
          </Row>
          <Row>
            <Col span={16}></Col>
          </Row>
        </Col>
      </Row>
    </>
  );
};

export default PaliChapterCardWidget;
