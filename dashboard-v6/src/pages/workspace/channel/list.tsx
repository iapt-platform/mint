import { useNavigate, useSearchParams } from "react-router";
import { useIntl } from "react-intl";

import ChannelTable, {
  CHANNEL_LIST,
  type IChannelListQuery,
} from "../../../components/channel/ChannelTable";
import { loadPageSize } from "../../../utils/pageSize";
import type { IChannel } from "../../../api/channel";
import { useActiveStudio } from "../../../hooks/useActiveStudio";

const TABS: IChannelListQuery["tab"][] = ["my", "collaboration", "community"];
const ORDERS: NonNullable<IChannelListQuery["order"]>[] = [
  "created_at",
  "updated_at",
  "name",
  "progress",
];

/**
 * 工作区的 channel 列表。页签、页码、每页条数、关键字、协作者、排序都放在 URL 里，
 * 刷新（F5）或分享链接都能回到同一个视图。每页条数另记在 localStorage：URL 没带时用记下的值，
 * 等于记下的值时也不写进 URL。
 * `?tab=collaboration&page=2&pagesize=20&q=pali&collaborator=<uuid>&order=created_at&dir=asc`
 */
const Widget = () => {
  const { studioId, studioName } = useActiveStudio();
  const navigate = useNavigate();
  const intl = useIntl();
  const [searchParams, setSearchParams] = useSearchParams();

  const defaultPageSize = loadPageSize(CHANNEL_LIST);
  const tab = searchParams.get("tab") as IChannelListQuery["tab"];
  const order = searchParams.get("order") as IChannelListQuery["order"];
  const query: IChannelListQuery = {
    tab: TABS.includes(tab) ? tab : "my",
    page: Math.max(1, Number(searchParams.get("page")) || 1),
    pageSize: Math.max(
      1,
      Number(searchParams.get("pagesize")) || defaultPageSize,
    ),
    q: searchParams.get("q") || undefined,
    collaborator: searchParams.get("collaborator") || undefined,
    order: order && ORDERS.includes(order) ? order : undefined,
    dir: searchParams.get("dir") === "asc" ? "asc" : order ? "desc" : undefined,
  };

  const handleQueryChange = (next: Partial<IChannelListQuery>) => {
    setSearchParams((prev) => {
      const params = new URLSearchParams(prev);
      const merged = { ...query, ...next };
      // 改每页条数时 ChannelTable 已把新值记进 localStorage，这里再读就是新的默认值
      const savedPageSize = loadPageSize(CHANNEL_LIST);
      const values: Record<string, string | undefined> = {
        tab: merged.tab === "my" ? undefined : merged.tab,
        page: merged.page > 1 ? String(merged.page) : undefined,
        pagesize:
          merged.pageSize !== savedPageSize
            ? String(merged.pageSize)
            : undefined,
        q: merged.q,
        collaborator: merged.collaborator,
        order: merged.order,
        dir: merged.order ? merged.dir : undefined,
      };
      // 默认值不写进 URL，地址栏保持干净
      Object.entries(values).forEach(([key, value]) =>
        value ? params.set(key, value) : params.delete(key),
      );
      return params;
    });
  };

  return (
    <>
      <title>
        {intl.formatMessage({ id: "columns.studio.channel.title" })}
      </title>
      <ChannelTable
        query={query}
        onQueryChange={handleQueryChange}
        studioId={studioId}
        studioName={studioName}
        onSelect={(channel: IChannel) => {
          navigate(`/workspace/channel/${channel.id}`);
        }}
      />
    </>
  );
};

export default Widget;
