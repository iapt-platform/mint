import { Link } from "react-router-dom";
import { Button, Layout, Space } from "antd";
import { FormattedMessage } from "react-intl";
import type { MenuProps } from "antd";
import { Menu } from "antd";
import { SearchOutlined } from "@ant-design/icons";

import img_banner from "../../assets/library/images/wikipali_logo_library.svg";
import UiLangSelect from "../general/UiLangSelect";
import SignInAvatar from "../auth/SignInAvatar";
import ToStudio from "../auth/ToStudio";
import ThemeSelect from "../general/ThemeSelect";

const { Header } = Layout;

const onClick: MenuProps["onClick"] = (e) => {
  console.log("click ", e);
};
export const mainMenuItems: MenuProps["items"] = [
  {
    label: (
      <Link to="/community/list">
        <FormattedMessage id="columns.library.community.title" />
      </Link>
    ),
    key: "community",
  },
  {
    label: (
      <Link to="/palicanon/list">
        <FormattedMessage id="columns.library.palicanon.title" />
      </Link>
    ),
    key: "palicanon",
  },
  {
    label: (
      <Link to="/course/list">
        <FormattedMessage id="columns.library.course.title" />
      </Link>
    ),
    key: "course",
  },
  {
    label: (
      <Link to="/dict/recent">
        <FormattedMessage id="columns.library.dict.title" />
      </Link>
    ),
    key: "dict",
  },
  {
    label: (
      <Link to="/anthology/list">
        <FormattedMessage id="columns.library.anthology.title" />
      </Link>
    ),
    key: "anthology",
  },
  {
    label: (
      <a
        href="https://assets-hk.wikipali.org/help/zh-Hans/"
        target="_blank"
        rel="noreferrer"
      >
        <FormattedMessage id="columns.library.help.title" />
      </a>
    ),
    key: "help",
  },

  {
    label: (
      <a
        href="https://assets-hk.wikipali.org/pali-handbook/zh-Hans/"
        target="_blank"
        rel="noreferrer"
      >
        <FormattedMessage id="columns.library.palihandbook.title" />
      </a>
    ),
    key: "palihandbook",
  },
  {
    label: (
      <Link
        to="https://www-hk.wikipali.org/app/calendar/index.html"
        target={"_blank"}
      >
        <FormattedMessage id="columns.library.calendar.title" />
      </Link>
    ),
    key: "calendar",
  },
  {
    label: (
      <Link
        to="https://www-hk.wikipali.org/app/tools/unicode.html"
        target={"_blank"}
      >
        <FormattedMessage id="columns.library.convertor.title" />
      </Link>
    ),
    key: "convertor",
  },
  {
    label: (
      <Link to="https://www-hk.wikipali.org/app/statistics/" target={"_blank"}>
        <FormattedMessage id="columns.library.statistics.title" />
      </Link>
    ),
    key: "statistics",
  },
  {
    label: <Link to="/discussion/list">Discussion(alpha)</Link>,
    key: "discussion",
  },
];
type IWidgetHeadBar = {
  selectedKeys?: string;
};
const HeadBarWidget = ({ selectedKeys = "" }: IWidgetHeadBar) => {
  //Library head bar
  // TODO

  return (
    <Header
      className="header"
      style={{
        lineHeight: "44px",
        height: 44,
        paddingLeft: 10,
        paddingRight: 10,
      }}
    >
      <div
        style={{
          display: "flex",
          width: "100%",
          justifyContent: "space-between",
        }}
      >
        <div style={{ width: 100 }}>
          <Link to="/">
            <img alt="code" style={{ height: "3em" }} src={img_banner} />
          </Link>
        </div>
        <div style={{ width: 500 }}>
          <Menu
            onClick={onClick}
            selectedKeys={[selectedKeys]}
            mode="horizontal"
            theme="dark"
            items={mainMenuItems}
          />
        </div>
        <div>
          <Space>
            <Link to="/download/download">下载</Link>
            <Link to="/search/home">
              <Button
                style={{ color: "white" }}
                icon={<SearchOutlined />}
                type="text"
              />
            </Link>
            <ToStudio />
            <SignInAvatar />
            <UiLangSelect />
            <ThemeSelect />
          </Space>
        </div>
      </div>
    </Header>
  );
};

export default HeadBarWidget;
