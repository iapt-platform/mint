import { Link } from "react-router-dom";
import { Col, Row, Input, Layout, Space } from "antd";

import img_banner from "../../assets/studio/images/wikipali_banner.svg";
import UiLangSelect from "../general/UiLangSelect";
import SignInAvatar from "../auth/SignInAvatar";
import ToLibaray from "../auth/ToLibaray";

const { Search } = Input;
const { Header } = Layout;

const onSearch = (value: string) => console.log(value);

const Widget = () => {
  return (
    <Header className="header" style={{ lineHeight: "44px", height: 44 }}>
      <Row justify="space-between">
        <Col flex="80px">
          <Link to="/">
            <img alt="code" style={{ height: 36 }} src={img_banner} />
          </Link>
        </Col>
        <Col span={8}>
          <Search
            placeholder="input search text"
            onSearch={onSearch}
            style={{ width: "100%" }}
          />
        </Col>
        <Col span={4}>
          <Space>
            <ToLibaray />
            <SignInAvatar />
            <UiLangSelect />
          </Space>
        </Col>
      </Row>
    </Header>
  );
};

export default Widget;
