import { ActionType, ProList } from "@ant-design/pro-components";
import { Button } from "antd";

import { ITagMapData, ITagMapResponseList } from "../api/Tag";
import { getSorterUrl } from "../../utils";
import { get } from "../../request";
import { useRef } from "react";
import { useIntl } from "react-intl";

interface IWidget {
  tagId?: string;
  onSelect?: Function;
}

const TagsList = ({ tagId, onSelect }: IWidget) => {
  const intl = useIntl(); //i18n
  const ref = useRef<ActionType>();
  const pageSize = 10;

  return (
    <ProList<ITagMapData>
      actionRef={ref}
      search={{
        filterType: "light",
      }}
      rowKey="name"
      request={async (params = {}, sorter, filter) => {
        console.log(params, sorter, filter);
        let url = `/v2/tag-map?view=items&tag_id=${tagId}`;
        const offset =
          ((params.current ? params.current : 1) - 1) *
          (params.pageSize ? params.pageSize : pageSize);
        url += `&limit=${params.pageSize}&offset=${offset}`;
        url += params.keyword ? "&search=" + params.keyword : "";

        url += getSorterUrl(sorter);

        console.info("api request", url);
        const res = await get<ITagMapResponseList>(url);
        console.info("api response", res);
        return {
          total: res.data.count,
          succcess: true,
          data: res.data.rows,
        };
      }}
      pagination={{
        pageSize: pageSize,
      }}
      options={{
        search: true,
      }}
      metas={{
        title: {
          dataIndex: "title",
          title: "title",
          search: false,
          render(dom, entity, index, action, schema) {
            return <>{entity.title}</>;
          },
        },
        subTitle: {
          dataIndex: "description",
          search: false,
        },
        actions: {
          render: (text, row) => [
            <Button>{"edit"}</Button>,
            <Button danger>{"delete"}</Button>,
          ],
          search: false,
        },
        status: {
          // 自己扩展的字段，主要用于筛选，不在列表中显示
          title: "排序",
          valueType: "select",
          valueEnum: {
            all: { text: "全部", status: "Default" },
            open: {
              text: "未解决",
              status: "Error",
            },
            closed: {
              text: "已解决",
              status: "Success",
            },
            processing: {
              text: "解决中",
              status: "Processing",
            },
          },
        },
      }}
    />
  );
};

export default TagsList;
