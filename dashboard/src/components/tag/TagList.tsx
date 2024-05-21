import { ActionType, ProList } from "@ant-design/pro-components";
import { Button, Popover, Tag } from "antd";
import { PlusOutlined } from "@ant-design/icons";

import { ITagData, ITagResponseList } from "../../components/api/Tag";
import { getSorterUrl } from "../../utils";
import { get } from "../../request";
import { useRef, useState } from "react";
import TagCreate from "./TagCreate";
import { useIntl } from "react-intl";
import { Link } from "react-router-dom";

/**
 * 10进制数字转为16进制字符串
 * @param {number} arg
 * @returns
 */
/*
作者：sq800
链接：https://juejin.cn/post/7250029395024281656
来源：稀土掘金
著作权归作者所有。商业转载请联系作者获得授权，非商业转载请注明出处。
*/
export const numToHex = (arg: number) => {
  try {
    let a = arg.toString(16).toUpperCase();
    return a.length % 2 === 1 ? "0" + a : a;
  } catch (e) {
    console.warn("数字转16进制出错:", e);
  }
};

interface IWidget {
  studioName?: string;
  readonly?: boolean;
  onSelect?: Function;
}

const TagsList = ({ studioName, readonly = false, onSelect }: IWidget) => {
  const intl = useIntl(); //i18n
  const ref = useRef<ActionType>();
  const [openCreate, setOpenCreate] = useState(false);
  return (
    <ProList<ITagData>
      actionRef={ref}
      toolBarRender={() => {
        return readonly
          ? [
              <Link to={`/studio/${studioName}/tags/list`} target="_blank">
                {intl.formatMessage({ id: "buttons.manage" })}
              </Link>,
            ]
          : [
              <Popover
                content={
                  <TagCreate
                    studio={studioName}
                    onCreate={() => {
                      //新建课程成功后刷新

                      ref.current?.reload();
                      setOpenCreate(false);
                    }}
                  />
                }
                title="Create"
                placement="bottomRight"
                trigger="click"
                open={openCreate}
                onOpenChange={(newOpen: boolean) => {
                  setOpenCreate(newOpen);
                }}
              >
                <Button key="button" icon={<PlusOutlined />} type="primary">
                  {intl.formatMessage({ id: "buttons.create" })}
                </Button>
              </Popover>,
            ];
      }}
      search={{
        filterType: "light",
      }}
      rowKey="name"
      request={async (params = {}, sorter, filter) => {
        console.log(params, sorter, filter);
        let url = `/v2/tag?view=studio&name=${studioName}`;
        const offset =
          ((params.current ? params.current : 1) - 1) *
          (params.pageSize ? params.pageSize : 20);
        url += `&limit=${params.pageSize}&offset=${offset}`;
        url += params.keyword ? "&search=" + params.keyword : "";

        url += getSorterUrl(sorter);

        console.info("api request", url);
        const res = await get<ITagResponseList>(url);
        console.info("api response", res);
        return {
          total: res.data.count,
          succcess: true,
          data: res.data.rows,
        };
      }}
      pagination={{
        pageSize: 10,
      }}
      options={{
        search: true,
      }}
      metas={{
        title: {
          dataIndex: "name",
          title: "用户",
          search: false,
          render(dom, entity, index, action, schema) {
            return (
              <Tag
                color={"#" + numToHex(entity.color ?? 13684944)}
                onClick={() => {
                  if (typeof onSelect !== "undefined") {
                    onSelect(entity);
                  }
                }}
              >
                {entity.name}
              </Tag>
            );
          },
        },
        subTitle: {
          dataIndex: "description",
          search: false,
        },
        actions: readonly
          ? undefined
          : {
              render: (dom, entity, index, action, schema) => [
                <Link to={`/studio/${studioName}/tags/${entity.id}/edit`}>
                  {"edit"}
                </Link>,
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
