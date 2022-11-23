import { useIntl } from "react-intl";
import { Badge, Dropdown } from "antd";
import {
  OneToOneOutlined,
  LinkOutlined,
  CalendarOutlined,
} from "@ant-design/icons";

import store from "../../../store";
import {
  ISite,
  refresh as refreshLayout,
} from "../../../reducers/open-article";
import type { MenuProps } from "antd";

const handleButtonClick = (e: React.MouseEvent<HTMLButtonElement>) => {
  console.log("click left button", e);
};

interface IWidgetSentTabButton {
  icon?: JSX.Element;
  type: string;
  sentId: string;
  count?: number;
}
const Widget = ({ icon, type, sentId, count = 0 }: IWidgetSentTabButton) => {
  const intl = useIntl();
  const items: MenuProps["items"] = [
    {
      label: "在分栏中打开",
      key: "openInCol",
      icon: <OneToOneOutlined />,
    },
    {
      label: "在新标签页中打开",
      key: "openInWin",
      icon: <CalendarOutlined />,
    },
    {
      label: "复制链接",
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
      size="small"
      type="text"
      menu={menuProps}
      onClick={handleButtonClick}
    >
      {intl.formatMessage({
        id: `channel.type.${type}.label`,
      })}
      <Badge size="small" color="geekblue" count={count}></Badge>
    </Dropdown.Button>
  );
};

export default Widget;
