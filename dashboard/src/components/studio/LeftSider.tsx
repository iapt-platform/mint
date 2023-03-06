import { Link } from "react-router-dom";
import { useIntl } from "react-intl";
import { useParams } from "react-router-dom";
import type { MenuProps } from "antd";
import { Affix, Layout } from "antd";
import { Menu } from "antd";
import {
  AppstoreOutlined,
  HomeOutlined,
  TeamOutlined,
} from "@ant-design/icons";

const { Sider } = Layout;

const onClick: MenuProps["onClick"] = (e) => {
  console.log("click ", e);
};

type IWidgetHeadBar = {
  selectedKeys?: string;
};
const Widget = ({ selectedKeys = "" }: IWidgetHeadBar) => {
  //Library head bar
  const intl = useIntl(); //i18n
  const { studioname } = useParams();
  const linkPalicanon = "/studio/" + studioname + "/palicanon";
  const linkRecent = "/studio/" + studioname + "/recent";
  const linkChannel = "/studio/" + studioname + "/channel/list";
  const linkGroup = "/studio/" + studioname + "/group/list";
  const linkUserdict = "/studio/" + studioname + "/dict/list";
  const linkTerm = "/studio/" + studioname + "/term/list";
  const linkArticle = "/studio/" + studioname + "/article/list";
  const linkAnthology = "/studio/" + studioname + "/anthology/list";
  const linkAnalysis = "/studio/" + studioname + "/exp/list";
  const linkCourse = "/studio/" + studioname + "/course/list";
  const linkSetting = "/studio/" + studioname + "/setting";

  const items: MenuProps["items"] = [
    {
      label: "常用",
      key: "basic",
      icon: <HomeOutlined />,
      children: [
        {
          label: (
            <Link to={linkPalicanon}>
              {intl.formatMessage({
                id: "columns.studio.palicanon.title",
              })}
            </Link>
          ),
          key: "palicanon",
          disabled: true,
        },
        {
          label: (
            <Link to={linkRecent}>
              {intl.formatMessage({
                id: "columns.studio.recent.title",
              })}
            </Link>
          ),
          key: "recent",
          disabled: true,
        },
        {
          label: (
            <Link to={linkChannel}>
              {intl.formatMessage({
                id: "columns.studio.channel.title",
              })}
            </Link>
          ),
          key: "channel",
        },
        {
          label: (
            <Link to={linkAnalysis}>
              {intl.formatMessage({
                id: "columns.exp.title",
              })}
            </Link>
          ),
          key: "analysis",
        },
      ],
    },
    {
      label: "高级",
      key: "advance",
      icon: <AppstoreOutlined />,
      children: [
        {
          label: (
            <Link to={linkCourse}>
              {intl.formatMessage({
                id: "columns.library.course.title",
              })}
            </Link>
          ),
          key: "course",
        },
        {
          label: (
            <Link to={linkUserdict}>
              {intl.formatMessage({
                id: "columns.studio.userdict.title",
              })}
            </Link>
          ),
          key: "userdict",
        },
        {
          label: (
            <Link to={linkTerm}>
              {intl.formatMessage({
                id: "columns.studio.term.title",
              })}
            </Link>
          ),
          key: "term",
        },
        {
          label: (
            <Link to={linkArticle}>
              {intl.formatMessage({
                id: "columns.studio.article.title",
              })}
            </Link>
          ),
          key: "article",
        },
        {
          label: (
            <Link to={linkAnthology}>
              {intl.formatMessage({
                id: "columns.studio.anthology.title",
              })}
            </Link>
          ),
          key: "anthology",
        },
        {
          label: (
            <Link to={linkSetting}>
              {intl.formatMessage({
                id: "columns.studio.setting.title",
              })}
            </Link>
          ),
          key: "setting",
          children: [
            {
              label: "账户",
              key: "account",
            },
            {
              label: "显示",
              key: "display",
            },
          ],
        },
      ],
    },
    {
      label: "协作",
      key: "collaboration",
      icon: <TeamOutlined />,
      children: [
        {
          label: (
            <Link to={linkGroup}>
              {intl.formatMessage({
                id: "columns.studio.group.title",
              })}
            </Link>
          ),
          key: "group",
        },
      ],
    },
  ];

  return (
    <Affix offsetTop={0}>
      <Sider width={200} breakpoint="lg" className="site-layout-background">
        <Menu
          theme="light"
          onClick={onClick}
          defaultSelectedKeys={[selectedKeys]}
          defaultOpenKeys={["basic", "advance", "collaboration"]}
          mode="inline"
          items={items}
        />
      </Sider>
    </Affix>
  );
};

export default Widget;
