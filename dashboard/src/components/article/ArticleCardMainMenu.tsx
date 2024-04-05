import { Tabs, Button, Popover } from "antd";
import { MenuOutlined, PushpinOutlined } from "@ant-design/icons";

import PaliTextToc from "./PaliTextToc";
import Find from "./Find";
import Nav from "./Nav";
import { useIntl } from "react-intl";

interface IWidget {
  type?: string;
  articleId?: string;
}
const ArticleCardMainMenuWidget = ({ type, articleId }: IWidget) => {
  const intl = useIntl();
  const id = articleId?.split("_");
  let tocWidget = <></>;
  if (id && id.length > 0) {
    const sentId = id[0].split("-");
    if (sentId.length > 1) {
      tocWidget = (
        <PaliTextToc book={parseInt(sentId[0])} para={parseInt(sentId[1])} />
      );
    }
  }
  const styleTabBody: React.CSSProperties = {
    width: 350,
    height: "calc(100vh - 200px)",
    overflowY: "scroll",
  };
  const mainMenuContent = (
    <Tabs
      size="small"
      defaultActiveKey="1"
      tabBarExtraContent={{
        right: <Button type="text" size="small" icon={<PushpinOutlined />} />,
      }}
      items={[
        {
          label: intl.formatMessage({
            id: "labels.table-of-content",
          }),
          key: "1",
          children: <div style={styleTabBody}>{tocWidget}</div>,
        },
        {
          label: `定位`,
          key: "2",
          children: (
            <div style={styleTabBody}>
              <Nav />
            </div>
          ),
        },
        {
          label: `查找`,
          key: "3",
          children: (
            <div style={styleTabBody}>
              <Find />
            </div>
          ),
        },
      ]}
    />
  );
  return (
    <Popover
      placement="bottomLeft"
      arrowPointAtCenter
      content={mainMenuContent}
      trigger="click"
    >
      <Button size="small" icon={<MenuOutlined />} />
    </Popover>
  );
};

export default ArticleCardMainMenuWidget;
