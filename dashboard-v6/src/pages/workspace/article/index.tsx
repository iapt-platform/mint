import { useIntl } from "react-intl";
import { useSearchParams } from "react-router";
import ArticleList from "../../../components/article/ArticleList";
import { useActiveStudio } from "../../../hooks/useActiveStudio";

const Widget = () => {
  const { studioName } = useActiveStudio();
  const intl = useIntl();

  const [searchParams, setSearchParams] = useSearchParams();

  const tab = searchParams.get("tab") ?? "my";
  const page = Number(searchParams.get("page") ?? 1);
  const pageSize = Number(searchParams.get("pagesize") ?? 10);

  const handleTabChange = (newTab: string) => {
    setSearchParams((prev) => {
      const next = new URLSearchParams(prev);
      next.set("tab", newTab);
      next.set("page", "1"); // 切 tab 重置页码
      return next;
    });
  };

  const handlePageChange = (newPage: number, newPageSize: number) => {
    setSearchParams((prev) => {
      const next = new URLSearchParams(prev);
      next.set("page", String(newPage));
      next.set("pagesize", String(newPageSize));
      return next;
    });
  };

  console.debug("article list", studioName);
  return (
    <>
      <title>
        {intl.formatMessage({ id: "columns.studio.article.title" })}
      </title>
      <ArticleList
        tab={tab}
        page={page}
        pageSize={pageSize}
        onTabChange={handleTabChange}
        onPageChange={handlePageChange}
        studioName={studioName}
      />
    </>
  );
};

export default Widget;
