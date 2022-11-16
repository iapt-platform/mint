import { useState } from "react";
import { useParams } from "react-router-dom";
import { Col, Row, Switch } from "antd";
import { Button, Drawer, Space } from "antd";
import { SettingOutlined } from "@ant-design/icons";

import Article, { ArticleMode } from "../../../components/article/Article";
import ArticleCard from "../../../components/article/ArticleCard";

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

  const [articleMode2, setArticleMode2] = useState<ArticleMode>("read");

  const showDrawer = () => {
    setOpen(true);
  };

  const onClose = () => {
    setOpen(false);
  };

  return (
    <div
      className="site-drawer-render-in-current-wrapper"
      style={{ display: "flex" }}
    >
      <Row>
        <Col flex="auto">
          <Row>
            <Col span="11">
              <div>
                <ArticleCard
                  onModeChange={(e: ArticleMode) => {
                    setArticleMode(e);
                  }}
                >
                  <Article
                    active={true}
                    type={`corpus/${type}`}
                    articleId={id}
                    mode={articleMode}
                  />
                </ArticleCard>
              </div>
            </Col>
            <Col span="11">
              <div>
                <ArticleCard
                  onModeChange={(e: ArticleMode) => {
                    setArticleMode2(e);
                  }}
                >
                  <div>
                    <Article
                      active={false}
                      type={`corpus/${type}`}
                      articleId={id}
                      mode={articleMode2}
                    />
                  </div>
                </ArticleCard>
              </div>
            </Col>
          </Row>
        </Col>
        <Col flex="24px">
          <Button
            shape="circle"
            icon={<SettingOutlined />}
            onClick={showDrawer}
          />
        </Col>
      </Row>

      <Drawer
        title="Setting"
        placement="right"
        onClose={onClose}
        open={open}
        getContainer={false}
        style={{ position: "absolute" }}
      >
        <Space>
          保存到用户设置
          <Switch
            defaultChecked
            onChange={(checked) => {
              console.log(checked);
            }}
          />
        </Space>
        <Space>
          显示原文
          <Switch
            defaultChecked
            onChange={(checked) => {
              console.log(checked);
            }}
          />
        </Space>
        <Space>
          点词查询
          <Switch
            defaultChecked
            onChange={(checked) => {
              console.log(checked);
            }}
          />
        </Space>
      </Drawer>
    </div>
  );
};

export default Widget;
