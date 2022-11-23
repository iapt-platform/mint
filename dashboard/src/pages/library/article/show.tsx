import { useRef, useState } from "react";
import { useParams } from "react-router-dom";
import { Switch } from "antd";
import { Drawer, Space, Radio } from "antd";
import {
  SettingOutlined,
  ProfileOutlined,
  ShoppingCartOutlined,
} from "@ant-design/icons";

import Article, { ArticleMode } from "../../../components/article/Article";
import ArticleCard from "../../../components/article/ArticleCard";
import ArticleTabs from "../../../components/article/ArticleTabs";
import SettingArticle from "../../../components/auth/setting/SettingArticle";

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

/**
 * type:
 *   sent 句子
 *   sim  相似句
 *   v_para vri 自然段
 *   page  页码
 *   chapter 段落
 *   article 文章
 * @returns
 */
const Widget = () => {
  const { type, id, mode = "read" } = useParams(); //url 参数
  const [open, setOpen] = useState(false);
  const [articleMode, setArticleMode] = useState<ArticleMode>(
    mode as ArticleMode
  );
  const [value2, setValue2] = useState(0);

  const box = useRef<HTMLDivElement>(null);

  const closeCol = () => {
    if (box.current) {
      box.current.style.display = "none";
    }
  };
  const openCol = () => {
    if (box.current) {
      box.current.style.display = "block";
    }
  };

  const onClose = () => {
    setOpen(false);
  };
  const rightBarWidth = "40px";
  return (
    <div className="site-drawer-render-in-current-wrapper">
      <div style={{ width: "100%", display: "flex" }}>
        <div style={{ width: `calc(100%-${rightBarWidth})`, display: "flex" }}>
          <div style={{ flex: 5 }}>
            <ArticleCard
              onModeChange={(e: ArticleMode) => {
                setArticleMode(e);
              }}
              showCol={openCol}
            >
              <Article
                active={true}
                type={`corpus/${type}`}
                articleId={id}
                mode={articleMode}
              />
            </ArticleCard>
          </div>
          <div style={{ flex: 5 }} ref={box}>
            <ArticleTabs onClose={closeCol} />
          </div>
          <div style={{ width: 300, backgroundColor: "wheat" }}>{setting}</div>
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
              setValue2(e.target.value);
            }}
          >
            <Space direction="vertical">
              <Radio
                value={3}
                onClick={() => {
                  if (value2 === 3) {
                    setValue2(6);
                  }
                }}
              >
                <SettingOutlined />
              </Radio>
              <Radio
                value={4}
                onClick={() => {
                  if (value2 === 4) {
                    setValue2(6);
                  }
                }}
              >
                <ProfileOutlined />
              </Radio>
              <Radio
                value={5}
                onClick={() => {
                  if (value2 === 5) {
                    setValue2(6);
                  }
                }}
              >
                <ShoppingCartOutlined />
              </Radio>
              <Radio value={6} style={{ display: "none" }}>
                <></>
              </Radio>
            </Space>
          </Radio.Group>
        </div>
      </div>

      <Drawer
        title="Setting"
        placement="right"
        onClose={onClose}
        open={open}
        getContainer={false}
        style={{ position: "absolute" }}
      ></Drawer>
    </div>
  );
};

export default Widget;
