import { Button, Popover } from "antd";
import { Typography } from "antd";
import { SearchOutlined, CopyOutlined } from "@ant-design/icons";
import { ProCard } from "@ant-design/pro-components";
import { useIntl } from "react-intl";

const { Text, Link } = Typography;

interface IWidgetQuoteCtl {
  paraId: string;
  paliPath?: string[];
  channel?: string;
  pali?: string;
  error?: boolean;
  message?: string;
}
const QuoteCtl = ({
  paraId,
  paliPath,
  channel,
  pali,
  error,
  message,
}: IWidgetQuoteCtl) => {
  const intl = useIntl();
  const show = pali ? pali : paraId;
  let textShow = <></>;

  if (typeof error !== "undefined") {
    textShow = <Text type="danger">{show}</Text>;
  } else {
    textShow = <Link>{show}</Link>;
  }

  const userCard = (
    <>
      <ProCard
        style={{ maxWidth: 500, minWidth: 300 }}
        actions={[
          <Button type="link" size="small" icon={<SearchOutlined />}>
            分栏打开
          </Button>,
          <Button type="link" size="small" icon={<SearchOutlined />}>
            {intl.formatMessage({
              id: "buttons.open.in.new.tab",
            })}
          </Button>,
          <Button type="link" size="small" icon={<CopyOutlined />}>
            复制引用
          </Button>,
        ]}
      >
        <div>{message ? message : ""}</div>
      </ProCard>
    </>
  );
  return (
    <>
      <Popover content={userCard} placement="bottom">
        {textShow}
      </Popover>
    </>
  );
};

interface IWidget {
  props: string;
}
const Widget = ({ props }: IWidget) => {
  const prop = JSON.parse(atob(props)) as IWidgetQuoteCtl;
  console.log(prop);
  return (
    <>
      <QuoteCtl {...prop} />
    </>
  );
};

export default Widget;
