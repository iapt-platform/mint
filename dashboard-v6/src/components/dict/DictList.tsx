import { Affix, Anchor, Button, Col, Popover, Row } from "antd";
import { UnorderedListOutlined } from "@ant-design/icons";
import type { IAnchorData } from "../../api/dict";
const { Link } = Anchor;

interface IWidgetDictList {
  data: IAnchorData[];
}
const DictListWidget = (prop: IWidgetDictList) => {
  const GetLink = (anchors: IAnchorData[]) => {
    return anchors.map((it, id) => {
      return (
        <Link key={id} href={it.href} title={it.title}>
          {it.children ? GetLink(it.children) : ""}
        </Link>
      );
    });
  };
  const dictNav = <Anchor offsetTop={50}>{GetLink(prop.data)}</Anchor>;
  return (
    <Row>
      <Col xs={0} sm={24}>
        {dictNav}
      </Col>
      <Col xs={24} sm={0}>
        <Affix offsetTop={50}>
          <Popover
            placement="bottomRight"
            arrow={{ pointAtCenter: true }}
            content={dictNav}
            trigger="click"
          >
            <Button
              type="primary"
              shape="circle"
              icon={<UnorderedListOutlined />}
            />
          </Popover>
        </Affix>
      </Col>
    </Row>
  );
};

export default DictListWidget;
