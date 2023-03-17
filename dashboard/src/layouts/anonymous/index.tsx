import { Outlet } from "react-router-dom";
import { Col, Layout, Row } from "antd";
import UiLangSelect from "../../components/general/UiLangSelect";
import img_banner from "../../assets/library/images/wikipali_logo_library.svg";

const Widget = () => {
  return (
    <>
      <Layout style={{ textAlign: "right", backgroundColor: "#3e3e3e" }}>
        <UiLangSelect />
      </Layout>
      <div style={{ paddingTop: "3em", backgroundColor: "#3e3e3e" }}>
        <Row>
          <Col flex="auto"></Col>
          <Col flex="400px" style={{ padding: "1em" }}>
            <img alt="logo" style={{ height: "5em" }} src={img_banner} />
          </Col>
          <Col flex="400px" style={{ padding: "1em" }}>
            <Outlet />
          </Col>
          <Col flex="auto"></Col>
        </Row>
      </div>
      <div>anonymous layout footer</div>
    </>
  );
};

export default Widget;
