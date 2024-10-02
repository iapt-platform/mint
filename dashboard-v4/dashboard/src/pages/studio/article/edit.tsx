import { useState } from "react";
import { useParams } from "react-router-dom";
import { Affix, Button, Card, Space } from "antd";
import { DoubleLeftOutlined, DoubleRightOutlined } from "@ant-design/icons";
import GoBack from "../../../components/studio/GoBack";
import ReadonlyLabel from "../../../components/general/ReadonlyLabel";

import ArticleEdit from "../../../components/article/ArticleEdit";
import ArticleEditTools from "../../../components/article/ArticleEditTools";
import Article from "../../../components/article/Article";

const Widget = () => {
  const { studioname, articleId } = useParams(); //url 参数
  const [title, setTitle] = useState("loading");
  const [readonly, setReadonly] = useState(false);
  const [parent, setParent] = useState<string>();
  const [showParent, setShowParent] = useState(false);
  return (
    <div style={{ display: "flex" }}>
      <Card
        style={{ width: "100%" }}
        title={
          <Space>
            <GoBack to={`/studio/${studioname}/article/list`} title={title} />
            {readonly ? <ReadonlyLabel /> : undefined}
          </Space>
        }
        extra={
          <Button
            onClick={() => setShowParent((origin) => !origin)}
            style={{ display: parent ? "inline-block" : "none" }}
          >
            源文件
            {showParent ? <DoubleRightOutlined /> : <DoubleLeftOutlined />}
          </Button>
        }
      >
        <ArticleEdit
          articleId={articleId}
          onReady={(
            title: string,
            readonly: boolean,
            studioName?: string,
            parentUid?: string
          ) => {
            setTitle(title);
            setReadonly(readonly);
            setParent(parentUid);
          }}
        />
      </Card>
      <div
        style={{
          width: 1000,
          display: showParent ? "block" : "none",
        }}
      >
        <Affix offsetTop={0}>
          <div
            style={{
              height: "100vh",
              overflowY: "scroll",
              paddingLeft: "0.5em",
              paddingRight: "0.5em",
            }}
          >
            <Article
              active={true}
              type={"article"}
              articleId={parent}
              mode="read"
            />
          </div>
        </Affix>
      </div>
    </div>
  );
};

export default Widget;
