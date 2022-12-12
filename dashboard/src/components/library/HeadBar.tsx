import { Link } from "react-router-dom";
import { Layout, Col, Row, Space } from "antd";
import { useIntl } from "react-intl";
import type { MenuProps } from "antd";
import { Menu } from "antd";

import img_banner from "../../assets/library/images/wikipali_logo_library.svg";
import UiLangSelect from "../general/UiLangSelect";
import SignInAvatar from "../auth/SignInAvatar";
import ToStudio from "../auth/ToStudio";

const { Header } = Layout;

const onClick: MenuProps["onClick"] = (e) => {
  console.log("click ", e);
};

type IWidgetHeadBar = {
  selectedKeys?: string;
};
const Widget = ({ selectedKeys = "" }: IWidgetHeadBar) => {
  //Library head bar
  const intl = useIntl(); //i18n
  // TODO
  const items: MenuProps["items"] = [
    {
      label: (
        <Link to="/community/list">
          {intl.formatMessage({
            id: "columns.library.community.title",
          })}
        </Link>
      ),
      key: "community",
    },
    {
      label: (
        <Link to="/palicanon/list">
          {intl.formatMessage({
            id: "columns.library.palicanon.title",
          })}
        </Link>
      ),
      key: "palicanon",
    },
    {
      label: (
        <Link to="/course/list">
          {intl.formatMessage({ id: "columns.library.course.title" })}
        </Link>
      ),
      key: "course",
    },
    {
      label: (
        <Link to="/dict/recent">
          {intl.formatMessage({ id: "columns.library.dict.title" })}
        </Link>
      ),
      key: "dict",
    },
    {
      label: (
        <Link to="/anthology/list">
          {intl.formatMessage({
            id: "columns.library.anthology.title",
          })}
        </Link>
      ),
      key: "anthology",
    },
    {
      label: (
        <a
          href="https://asset-hk.wikipali.org/help/zh-Hans"
          target="_blank"
          rel="noreferrer"
        >
          {intl.formatMessage({ id: "columns.library.help.title" })}
        </a>
      ),
      key: "help",
    },
    {
      label: (
        <Space>
          {intl.formatMessage({ id: "columns.library.more.title" })}
        </Space>
      ),
      key: "more",
      children: [
        {
          label: (
            <a
              href="https://asset-hk.wikipali.org/handbook/zh-Hans"
              target="_blank"
              rel="noreferrer"
            >
              {intl.formatMessage({
                id: "columns.library.palihandbook.title",
              })}
            </a>
          ),
          key: "palihandbook",
        },
        {
          label: (
            <Link to="/calendar">
              {intl.formatMessage({
                id: "columns.library.calendar.title",
              })}
            </Link>
          ),
          key: "calendar",
        },
        {
          label: (
            <Link to="/convertor">
              {intl.formatMessage({
                id: "columns.library.convertor.title",
              })}
            </Link>
          ),
          key: "convertor",
        },
        {
          label: (
            <Link to="/statistics">
              {intl.formatMessage({
                id: "columns.library.statistics.title",
              })}
            </Link>
          ),
          key: "statistics",
        },
      ],
    },
  ];
  return (
    <Header className="header" style={{ lineHeight: "44px", height: 44 }}>
      <Row justify="space-between">
        <Col flex="100px">
          <Link to="/">
            <img alt="code" style={{ height: "3em" }} src={img_banner} />
          </Link>
        </Col>
        <Col span={8}>
          <Menu
            onClick={onClick}
            selectedKeys={[selectedKeys]}
            mode="horizontal"
            theme="dark"
            items={items}
          />
        </Col>
        <Col span={4}>
          <Space>
            <ToStudio />
            <SignInAvatar />
            <UiLangSelect />
          </Space>
        </Col>
      </Row>
    </Header>
  );
};

export default Widget;
