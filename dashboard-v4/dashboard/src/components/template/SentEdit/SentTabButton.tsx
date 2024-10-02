import { useIntl } from "react-intl";
import { Badge, Dropdown } from "antd";
import type { MenuProps } from "antd";
import { LinkOutlined, CalendarOutlined } from "@ant-design/icons";

import store from "../../../store";
import {
  ISite,
  refresh as refreshLayout,
} from "../../../reducers/open-article";

const handleButtonClick = (e: React.MouseEvent<HTMLButtonElement>) => {
  console.log("click left button", e);
};

interface IWidget {
  style?: React.CSSProperties;
  icon?: JSX.Element;
  type: string;
  sentId: string;
  count?: number;
  title?: string;
}
const SentTabButtonWidget = ({
  style,
  icon,
  type,
  sentId,
  title,
  count = 0,
}: IWidget) => {
  const intl = useIntl();
  const items: MenuProps["items"] = [
    {
      label: "在新标签页中打开",
      key: "openInWin",
      icon: <CalendarOutlined />,
    },
    {
      label: intl.formatMessage({
        id: "buttons.copy.link",
      }),
      key: "copyLink",
      icon: <LinkOutlined />,
    },
  ];
  const handleMenuClick: MenuProps["onClick"] = (e) => {
    e.domEvent.stopPropagation();
    switch (e.key) {
      case "openInCol":
        const it: ISite = {
          title: intl.formatMessage({
            id: `channel.type.${type}.label`,
          }),
          url: "corpus_sent/" + type,
          id: sentId,
        };
        store.dispatch(refreshLayout(it));
        break;
    }
  };
  const menuProps = {
    items,
    onClick: handleMenuClick,
  };

  return (
    <Dropdown.Button
      style={style}
      size="small"
      type="text"
      menu={menuProps}
      onClick={handleButtonClick}
    >
      {title}
      <Badge size="small" color="geekblue" count={count}></Badge>
    </Dropdown.Button>
  );
};

export default SentTabButtonWidget;
