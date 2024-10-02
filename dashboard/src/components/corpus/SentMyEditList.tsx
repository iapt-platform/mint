import { ProList } from "@ant-design/pro-components";
import { ISentenceData, ISentenceListResponse } from "../api/Corpus";
import { get } from "../../request";
import { channelTypeFilter } from "../channel/ChannelTable";
import MdView from "../template/MdView";

const SentMyEditList = () => {
  return (
    <ProList<ISentenceData>
      rowKey="id"
      search={{
        filterType: "light",
      }}
      options={{
        search: false,
      }}
      request={async (params = {}, sorter, filter) => {
        console.log(params, sorter, filter);
        let url = `/v2/sentence?view=my-edit&html=true`;
        const offset =
          ((params.current ? params.current : 1) - 1) *
          (params.pageSize ? params.pageSize : 20);
        url += `&limit=${params.pageSize}&offset=${offset}`;
        url += params.keyword ? "&search=" + params.keyword : "";
        console.info("api request", url);
        const res = await get<ISentenceListResponse>(url);
        console.debug("api response", res);
        return {
          total: res.data.count,
          succcess: true,
          data: res.data.rows,
        };
      }}
      pagination={{
        pageSize: 10,
      }}
      metas={{
        title: {
          dataIndex: "html",
          title: "译文",
          render(dom, entity, index, action, schema) {
            return <MdView html={entity.html} />;
          },
        },
        description: {
          dataIndex: "title",
          search: false,
        },

        status: {
          // 自己扩展的字段，主要用于筛选，不在列表中显示
          title: "状态",
          valueType: "select",
          valueEnum: channelTypeFilter,
        },
      }}
    />
  );
};

export default SentMyEditList;
