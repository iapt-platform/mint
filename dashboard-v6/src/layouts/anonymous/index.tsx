import { ConfigProvider, theme } from "antd";
import { Link, Outlet } from "react-router";

import UiLangSelect from "../../components/general/UiLangSelect";
import BeiAn from "../../components/general/BeiAn";
import img_logo from "../../assets/general/images/wikipali_logo.svg";

/**
 * 未登录页（登录 / 注册 / 找回密码）的外壳，布局参照 dashboard-v4 的 layouts/anonymous。
 *
 * logo 是白字 + 黄色图形，只能放在深色底上，所以底色固定为 v4 的 #3e3e3e，
 * 不跟随主题切换；顶栏与页脚套暗色算法，文字才看得清。
 * 中间的表单卡片仍跟随用户的主题。
 */
const BACKGROUND = "#3e3e3e";

const Widget = () => {
  return (
    <div
      style={{
        minHeight: "100vh",
        display: "flex",
        flexDirection: "column",
        backgroundColor: BACKGROUND,
      }}
    >
      <ConfigProvider theme={{ algorithm: theme.darkAlgorithm }}>
        <div
          style={{
            display: "flex",
            justifyContent: "flex-end",
            padding: "4px 16px",
          }}
        >
          <UiLangSelect />
        </div>
      </ConfigProvider>

      <div
        style={{
          flex: 1,
          display: "flex",
          flexWrap: "wrap",
          justifyContent: "center",
          alignItems: "flex-start",
          gap: 32,
          padding: "3em 16px 120px",
        }}
      >
        <div style={{ flex: "0 1 400px", padding: "1em 0" }}>
          <Link to="/">
            <img
              alt="wikipali"
              src={img_logo}
              style={{ width: "20em", maxWidth: "100%" }}
            />
          </Link>
        </div>
        <div style={{ flex: "0 1 auto", maxWidth: "100%" }}>
          <Outlet />
        </div>
      </div>

      <ConfigProvider theme={{ algorithm: theme.darkAlgorithm }}>
        <div
          style={{
            padding: "16px",
            textAlign: "center",
            color: "rgba(255, 255, 255, 0.65)",
          }}
        >
          <BeiAn />
        </div>
      </ConfigProvider>
    </div>
  );
};

export default Widget;
