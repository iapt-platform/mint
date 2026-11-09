import { Row, Col, Space } from "antd";
import { Typography } from "antd";
import { Tiny } from "@ant-design/plots";
import TocPath from "./TocPath";
import { useIntl } from "react-intl";

const { Title, Text, Link } = Typography;

export interface IPaliChapterData {
  Title: string;
  PaliTitle: string;
  level: number;
  Path: string;
  Book: number;
  Paragraph: number;
  chapterStrLen?: number;
  /** v2 `view=chapter` 不返回 chapter_len，此时为 undefined */
  paragraphCount?: number;
  progressLine?: number[];
}

/** 1234 → 1.2K，1234567 → 1.2M；小于 1000 原样。固定用 en，避免中文环境出「万」 */
const countFormatter = new Intl.NumberFormat("en", {
  notation: "compact",
  maximumFractionDigits: 1,
});
const formatCount = (n: number): string => countFormatter.format(n);

/** 统计行：标签灰色、数字正文色加粗，两组之间留空，不用竖线 */
const ChapterStats = ({
  data,
  fontSize,
}: {
  data: IPaliChapterData;
  fontSize?: number;
}) => {
  const intl = useIntl();
  // 接口不一定两项都给（v2 view=chapter 没有段落数），缺的那项不显示
  const items = [
    data.chapterStrLen != null && {
      label: intl.formatMessage({ id: "labels.char-count" }),
      value: formatCount(data.chapterStrLen),
    },
    data.paragraphCount != null && {
      label: intl.formatMessage({ id: "labels.paragraph-count" }),
      value: data.paragraphCount.toLocaleString("en"),
    },
  ].filter((it): it is { label: string; value: string } => Boolean(it));
  if (items.length === 0) return null;
  return (
    <Space size={12} style={{ fontSize }}>
      {items.map((it) => (
        <span key={it.label}>
          <Text type="secondary" style={{ fontSize }}>
            {it.label}
          </Text>{" "}
          <Text strong style={{ fontSize }}>
            {it.value}
          </Text>
        </span>
      ))}
    </Space>
  );
};

interface IWidget {
  data: IPaliChapterData;
  /** 紧凑模式：标题一行、统计一行，不显示路径，进度图缩小 */
  compact?: boolean;
  onTitleClick?: (e: React.MouseEvent<HTMLElement, MouseEvent>) => void;
}

const PaliChapterCardWidget = ({
  data,
  compact = false,
  onTitleClick,
}: IWidget) => {
  if (compact) {
    return (
      <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
        <div style={{ flex: "auto", minWidth: 0 }}>
          <Link
            strong
            onClick={(e) => {
              if (typeof onTitleClick !== "undefined") {
                onTitleClick(e);
              }
            }}
          >
            {data.Title}
          </Link>
          {data.PaliTitle !== data.Title ? (
            <Text type="secondary"> {data.PaliTitle}</Text>
          ) : null}
          <div>
            <ChapterStats data={data} fontSize={12} />
          </div>
        </div>
        {data.progressLine ? (
          <div style={{ flex: "none" }}>
            <Tiny.Line
              height={32}
              width={120}
              autoFit={false}
              data={data.progressLine}
              smooth={true}
            />
          </div>
        ) : null}
      </div>
    );
  }

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
                <Tiny.Line
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
              <ChapterStats data={data} />
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
