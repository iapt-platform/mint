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
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";

const { Sider } = Layout;

const onClick: MenuProps["onClick"] = (e) => {
  console.log("click ", e);
};

type IWidgetHeadBar = {
  selectedKeys?: string;
};
const LeftSiderWidget = ({ selectedKeys = "" }: IWidgetHeadBar) => {
  //Library head bar
  const user = useAppSelector(currentUser);

  const intl = useIntl(); //i18n
  const { studioname } = useParams();
  const linkRecent = "/studio/" + studioname + "/recent/list";
  const linkChannel = "/studio/" + studioname + "/channel/list";
  const linkGroup = "/studio/" + studioname + "/group/list";
  const linkUserdict = "/studio/" + studioname + "/dict/list";
  const linkTerm = "/studio/" + studioname + "/term/list";
  const linkArticle = "/studio/" + studioname + "/article/list";
  const linkAnalysis = "/studio/" + studioname + "/exp/list";
  const linkCourse = "/studio/" + studioname + "/course/list";
  const linkSetting = "/studio/" + studioname + "/setting";

  const items: MenuProps["items"] = [
    {
      label: intl.formatMessage({
        id: "columns.studio.basic.title",
      }),
      key: "basic",
      icon: <HomeOutlined />,
      children: [
        {
          label: (
            <Link to={`/studio/${studioname}/palicanon`}>
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
      label: intl.formatMessage({
        id: "columns.studio.advance.title",
      }),
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
            <Link to={`/studio/${studioname}/anthology/list`}>
              {intl.formatMessage({
                id: "columns.studio.anthology.title",
              })}
            </Link>
          ),
          key: "anthology",
        },
        {
          label: (
            <Link to={`/studio/${studioname}/attachment/list`}>
              {intl.formatMessage({
                id: "columns.studio.attachment.title",
              })}
            </Link>
          ),
          key: "attachment",
          disabled: user?.roles?.includes("uploader") ? false : true,
        },
        {
          label: (
            <Link to={`/studio/${studioname}/tags/list`}>
              {intl.formatMessage({
                id: "columns.studio.tag.title",
              })}
            </Link>
          ),
          key: "tag",
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
        },
      ].filter((value) => value.disabled !== true),
    },
    {
      label: intl.formatMessage({ id: "labels.collaboration" }),
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
          disabled: user?.roles?.includes("basic"),
        },
        {
          label: (
            <Link to={`/studio/${studioname}/invite/list`}>
              {intl.formatMessage({
                id: "columns.studio.invite.title",
              })}
            </Link>
          ),
          key: "invite",
          disabled: user?.roles?.includes("basic"),
        },
        {
          label: (
            <Link to={`/studio/${studioname}/transfer/list`}>
              {intl.formatMessage({
                id: "columns.studio.transfer.title",
              })}
            </Link>
          ),
          key: "transfer",
        },
      ],
    },
  ];

  return (
    <Affix offsetTop={0}>
      <div
        style={{
          height: "100vh",
          overflowY: "scroll",
        }}
      >
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
      </div>
    </Affix>
  );
};

export default LeftSiderWidget;
