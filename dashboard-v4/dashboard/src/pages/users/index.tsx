import { Outlet } from "react-router-dom";
import UiLangSelect from "../../components/general/UiLangSelect";
import { Footer } from "antd/lib/layout/layout";

const Widget = () => {
  return (
    <div style={{ minHeight: "100vh" }}>
      <div style={{ textAlign: "right", backgroundColor: "#3e3e3e" }}>
        <UiLangSelect />
      </div>
      <div
        style={{
          minHeight: "100vh",
          paddingTop: "1em",
          backgroundColor: "#3e3e3e",
        }}
      >
        <Outlet />
      </div>
      <Footer />
    </div>
  );
};

export default Widget;
