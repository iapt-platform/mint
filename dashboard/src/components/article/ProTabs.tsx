import { useRef, useState } from "react";
import { Switch } from "antd";
import { Radio, Space } from "antd";
import { SettingOutlined, ShoppingCartOutlined } from "@ant-design/icons";

import SettingArticle from "../auth/setting/SettingArticle";
import DictComponent from "../dict/DictComponent";
import { DictIcon, TermIcon } from "../../assets/icon";
import TermShell from "./TermShell";

const setting = (
  <>
    <Space>
      {"保存到用户设置"}
      <Switch
        defaultChecked
        onChange={(checked) => {
          console.log(checked);
        }}
      />
    </Space>
    <SettingArticle />
  </>
);

const ProTabsWidget = () => {
  const [value2, setValue2] = useState("close");
  const divSetting = useRef<HTMLDivElement>(null);
  const divDict = useRef<HTMLDivElement>(null);
  const divTerm = useRef<HTMLDivElement>(null);
  const divCart = useRef<HTMLDivElement>(null);
  const divPanel = useRef<HTMLDivElement>(null);
  const rightBarWidth = "48px";
  const closeAll = () => {
    if (divPanel.current) {
      divPanel.current.style.display = "none";
    }
  };
  const openPanel = () => {
    if (divPanel.current) {
      divPanel.current.style.display = "block";
    }
  };
  const headHeight = 64;
  const stylePanel: React.CSSProperties = {
    height: `calc(100vh - ${headHeight})`,
    overflowY: "scroll",
  };
  return (
    <div style={{ display: "flex" }}>
      <div ref={divPanel} style={{ width: 350, display: "none" }}>
        <div ref={divSetting} style={stylePanel}>
          {setting}
        </div>
        <div ref={divDict} style={stylePanel}>
          <DictComponent />
        </div>
        <div ref={divTerm} style={stylePanel}>
          <TermShell />
        </div>
        <div ref={divCart} style={stylePanel}></div>
      </div>
      <div
        style={{
          width: `${rightBarWidth}`,
          display: "flex",
          flexDirection: "column",
        }}
      >
        <Radio.Group
          value={value2}
          optionType="button"
          buttonStyle="solid"
          onChange={(e) => {
            console.log("radio change", e.target.value);
            if (divSetting.current) {
              divSetting.current.style.display = "none";
            }
            if (divDict.current) {
              divDict.current.style.display = "none";
            }
            if (divTerm.current) {
              divTerm.current.style.display = "none";
            }
            if (divCart.current) {
              divCart.current.style.display = "none";
            }
            switch (e.target.value) {
              case "setting":
                if (divSetting.current) {
                  divSetting.current.style.display = "block";
                }
                openPanel();
                break;
              case "dict":
                if (divDict.current) {
                  divDict.current.style.display = "block";
                }
                openPanel();

                break;
              case "term":
                if (divTerm.current) {
                  divTerm.current.style.display = "block";
                }
                openPanel();
                break;
              case "cart":
                if (divCart.current) {
                  divCart.current.style.display = "block";
                }
                openPanel();
                break;
              default:
                break;
            }
            setValue2(e.target.value);
          }}
        >
          <Space direction="vertical">
            <Radio
              value="setting"
              onClick={() => {
                if (value2 === "setting") {
                  setValue2("close");
                  closeAll();
                }
              }}
            >
              <SettingOutlined />
            </Radio>
            <Radio
              value="dict"
              onClick={() => {
                if (value2 === "dict") {
                  setValue2("close");
                  closeAll();
                }
              }}
            >
              <DictIcon />
            </Radio>
            <Radio
              value="term"
              onClick={() => {
                if (value2 === "term") {
                  setValue2("close");
                  closeAll();
                }
              }}
            >
              <TermIcon />
            </Radio>
            <Radio
              value="cart"
              onClick={() => {
                if (value2 === "cart") {
                  setValue2("close");
                  closeAll();
                }
              }}
            >
              <ShoppingCartOutlined />
            </Radio>
            <Radio value="close" style={{ display: "none" }}></Radio>
          </Space>
        </Radio.Group>
      </div>
    </div>
  );
};

export default ProTabsWidget;
