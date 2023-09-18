import { Link } from "react-router-dom";
import { useIntl } from "react-intl";
import { HomeOutlined } from "@ant-design/icons";
import type { MenuProps } from "antd";
import { Menu, Row, Col } from "antd";
import {
  AnthologyOutLinedIcon,
  CourseOutLinedIcon,
  TermOutlinedIcon,
  TranslationOutLinedIcon,
} from "../../assets/icon";

interface IWidgetBlogNav {
  selectedKey: string;
  studio?: string;
}
const BlogNavWidget = ({ selectedKey, studio }: IWidgetBlogNav) => {
  //Library head bar
  const intl = useIntl(); //i18n
  // TODO 换图标

  const items: MenuProps["items"] = [
    {
      label: (
        <Link to={`/blog/${studio}/overview`}>
          {intl.formatMessage({ id: "blog.overview" })}
        </Link>
      ),
      key: "overview",
      icon: <HomeOutlined />,
    },
    {
      label: (
        <Link to={`/blog/${studio}/palicanon`}>
          {intl.formatMessage({ id: "blog.palicanon" })}
        </Link>
      ),
      key: "palicanon",
      icon: <TranslationOutLinedIcon />,
    },
    {
      label: (
        <Link to={`/blog/${studio}/course`}>
          {intl.formatMessage({ id: "columns.library.course.title" })}
        </Link>
      ),
      key: "course",
      icon: <CourseOutLinedIcon />,
    },
    {
      label: (
        <Link to={`/blog/${studio}/anthology`}>
          {intl.formatMessage({ id: "columns.library.anthology.title" })}
        </Link>
      ),
      key: "anthology",
      icon: <AnthologyOutLinedIcon />,
    },
    {
      label: (
        <Link to={`/blog/${studio}/term`}>
          {intl.formatMessage({ id: "columns.library.term.title" })}
        </Link>
      ),
      key: "term",
      icon: <TermOutlinedIcon />,
    },
  ];
  return (
    <Row>
      <Col flex="300px"></Col>

      <Col flex="auto">
        <Menu selectedKeys={[selectedKey]} mode="horizontal" items={items} />
      </Col>
    </Row>
  );
};
export default BlogNavWidget;
