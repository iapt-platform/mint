import { Button, Space, Typography } from "antd";
import { DoubleRightOutlined, DoubleLeftOutlined } from "@ant-design/icons";

const { Paragraph, Text } = Typography;

const EllipsisMiddle: React.FC<{
  suffixCount: number;
  maxWidth: number;
  children?: string;
}> = ({ suffixCount, maxWidth = 500, children = "" }) => {
  const start = children.slice(0, children.length - suffixCount).trim();
  const suffix = children.slice(-suffixCount).trim();
  return (
    <Text style={{ maxWidth: maxWidth }} ellipsis={{ suffix }}>
      {start}
    </Text>
  );
};

interface IWidget {
  title?: string;
  prevTitle?: string;
  nextTitle?: string;
  onPrev?: Function;
  onNext?: Function;
}
const NavigateButtonWidget = ({
  title,
  prevTitle,
  nextTitle,
  onPrev,
  onNext,
}: IWidget) => {
  return (
    <Paragraph style={{ display: "flex", justifyContent: "space-between" }}>
      <Button
        disabled={typeof prevTitle === "undefined"}
        size="large"
        onClick={(event: React.MouseEvent<HTMLElement, MouseEvent>) => {
          if (typeof onPrev !== "undefined") {
            onPrev(event);
          }
        }}
      >
        <Space>
          <DoubleLeftOutlined />
          <EllipsisMiddle maxWidth={300} suffixCount={7}>
            {prevTitle}
          </EllipsisMiddle>
        </Space>
      </Button>
      <Text>{title}</Text>
      <Button
        size="large"
        disabled={typeof nextTitle === "undefined"}
        onClick={(event: React.MouseEvent<HTMLElement, MouseEvent>) => {
          if (typeof onNext !== "undefined") {
            onNext(event);
          }
        }}
      >
        <Space>
          <EllipsisMiddle maxWidth={300} suffixCount={7}>
            {nextTitle}
          </EllipsisMiddle>
          <DoubleRightOutlined />
        </Space>
      </Button>
    </Paragraph>
  );
};

export default NavigateButtonWidget;
