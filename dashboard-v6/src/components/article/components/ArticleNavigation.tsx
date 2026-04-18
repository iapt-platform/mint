import { useEffect, useState } from "react";
import { get } from "../../../request";
import type {
  ArticleType,
  IArticleNavData,
  IArticleNavResponse,
} from "../../../api/article";
import type { TTarget } from "../../../types";
import NavigateButton from "./NavigateButton";
import type { ITocPathNode } from "../../../api/pali-text";

interface IArticleNavigationProps {
  articleId?: string;
  anthologyId?: string | null;
  path?: ITocPathNode[];
  onArticleChange?: (type: ArticleType, id: string, target?: TTarget) => void;
}

const ArticleNavigation = ({
  articleId,
  anthologyId,
  path,
  onArticleChange,
}: IArticleNavigationProps) => {
  const [nav, setNav] = useState<IArticleNavData>();

  useEffect(() => {
    const url = `/api/v2/nav-article/${articleId}_${anthologyId}`;
    console.info("api request", url);
    get<IArticleNavResponse>(url)
      .then((json) => {
        console.debug("api response", json);
        if (json.ok) {
          setNav(json.data);
        }
      })
      .catch((e) => {
        console.error(e);
      });
  }, [anthologyId, articleId]);

  let endOfChapter = false;
  if (nav?.curr && nav?.next) {
    if (nav.curr.level > nav.next.level) {
      endOfChapter = true;
    }
  }

  let topOfChapter = false;
  if (nav?.curr && nav?.prev) {
    if (nav.curr.level > nav.prev.level) {
      topOfChapter = true;
    }
  }

  return (
    <NavigateButton
      prevTitle={nav?.prev?.title}
      nextTitle={nav?.next?.title}
      topOfChapter={topOfChapter}
      endOfChapter={endOfChapter}
      path={path}
      onNext={() => {
        if (onArticleChange && nav?.next?.article_id) {
          onArticleChange("article", nav.next.article_id);
        }
      }}
      onPrev={() => {
        if (onArticleChange && nav?.prev?.article_id) {
          onArticleChange("article", nav.prev.article_id);
        }
      }}
      onPathChange={(key: string) => {
        if (typeof onArticleChange !== "undefined") {
          const node = path?.find((value) => value.key === key);
          if (node) {
            let newType: ArticleType = "article";
            if (node.level === 0) {
              newType = "anthology";
            }
            if (node.key) {
              onArticleChange(newType, node.key, "_self");
            }
          }
        }
      }}
    />
  );
};

export default ArticleNavigation;
