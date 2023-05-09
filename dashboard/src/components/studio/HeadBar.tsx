import { Link } from "react-router-dom";
import { Input, Layout, Space } from "antd";

import img_banner from "../../assets/studio/images/wikipali_banner.svg";
import UiLangSelect from "../general/UiLangSelect";
import SignInAvatar from "../auth/SignInAvatar";
import ToLibrary from "../auth/ToLibrary";
import ThemeSelect from "../general/ThemeSelect";

const { Search } = Input;
const { Header } = Layout;

const onSearch = (value: string) => console.log(value);

const HeadBarWidget = () => {
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
        <div style={{ width: 80 }}>
          <Link to="/">
            <img alt="code" style={{ height: 36 }} src={img_banner} />
          </Link>
        </div>
        <div style={{ width: 500, lineHeight: 44 }}>
          <Search
            disabled
            placeholder="input search text"
            onSearch={onSearch}
            style={{ width: "100%" }}
          />
        </div>
        <div>
          <Space>
            <ToLibrary />
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
