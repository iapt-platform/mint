import { useIntl } from "react-intl";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";
import AnthologyList from "../../../components/anthology/AnthologyList";
import { useNavigate, useSearchParams } from "react-router";

const Widget = () => {
  const user = useAppSelector(currentUser);
  const studioName = user?.realName;
  const navigate = useNavigate();
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

  console.debug("anthology list", studioName);
  return (
    <>
      <title>
        {intl.formatMessage({ id: "columns.studio.anthology.title" })}
      </title>
      <AnthologyList
        tab={tab}
        page={page}
        pageSize={pageSize}
        onTabChange={handleTabChange}
        onPageChange={handlePageChange}
        studioName={studioName}
        onTitleClick={(id) => {
          navigate(`/workspace/anthology/${id}`);
        }}
      />
    </>
  );
};

export default Widget;
