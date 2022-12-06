import { useRef, useState } from "react";
import { useParams } from "react-router-dom";

import Article, { ArticleMode } from "../../../components/article/Article";
import ArticleCard from "../../../components/article/ArticleCard";
import ArticleTabs from "../../../components/article/ArticleTabs";
import ProTabs from "../../../components/library/article/ProTabs";

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
  const [articleMode, setArticleMode] = useState<ArticleMode>(
    mode as ArticleMode
  );

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

  const rightBarWidth = "48px";
  return (
    <div style={{ width: "100%", display: "flex" }}>
      <div style={{ width: `calc(100% - ${rightBarWidth})`, display: "flex" }}>
        <div style={{ flex: 5 }}>
          <ArticleCard
		  type={type}
		  articleId={id}
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
      </div>
      <div>
        <ProTabs />
      </div>
    </div>
  );
};

export default Widget;
