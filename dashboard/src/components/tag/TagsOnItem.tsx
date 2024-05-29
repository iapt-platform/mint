import { useIntl } from "react-intl";
import { ActionType, ProList } from "@ant-design/pro-components";
import {
  Button,
  Popconfirm,
  PopconfirmProps,
  Popover,
  Tag,
  message,
} from "antd";
import { PlusOutlined } from "@ant-design/icons";

import {
  ITagData,
  ITagMapData,
  ITagMapRequest,
  ITagMapResponse,
  ITagMapResponseList,
} from "../api/Tag";
import { getSorterUrl } from "../../utils";
import { delete_, get, post } from "../../request";
import { useRef, useState } from "react";

import TagsList, { numToHex } from "./TagList";
import { IDeleteResponse } from "../api/Article";
import store from "../../store";
import { tagsUpgrade } from "../../reducers/discussion-count";

interface IWidget {
  studioName?: string;
  courseId?: string;
  resId?: string;
  resType?: string;
  onSelect?: Function;
}

const TagsOnItem = ({
  studioName,
  courseId,
  resId,
  resType,
  onSelect,
}: IWidget) => {
  const intl = useIntl(); //i18n
  const ref = useRef<ActionType>();
  const [openCreate, setOpenCreate] = useState(false);

  const cancel: PopconfirmProps["onCancel"] = (e) => {
    console.debug(e);
  };

  return (
    <ProList<ITagMapData>
      actionRef={ref}
      toolBarRender={() => {
        return [
          <Popover
            overlayStyle={{ width: 300 }}
            content={
              <TagsList
                studioName={studioName}
                readonly
                onSelect={async (record: ITagData) => {
                  //新建记录
                  const url = "/v2/tag-map";
                  const data: ITagMapRequest = {
                    table_name: resType,
                    anchor_id: resId,
                    tag_id: record.id,
                    studio: studioName,
                    course: courseId,
                  };
                  const json = await post<ITagMapRequest, ITagMapResponse>(
                    url,
                    data
                  );
                  if (json.ok) {
                    //新建课程成功后刷新
                    ref.current?.reload();
                  } else {
                    console.error(json.message);
                  }
                  setOpenCreate(false);
                }}
              />
            }
            style={{ width: 300 }}
            title="select"
            placement="bottom"
            trigger="click"
            open={openCreate}
            onOpenChange={(newOpen: boolean) => {
              setOpenCreate(newOpen);
            }}
          >
            <Button key="button" icon={<PlusOutlined />} type="primary">
              {intl.formatMessage({ id: "buttons.add" })}
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
        let url = `/v2/tag-map?view=item&studio=${studioName}&res_id=${resId}`;
        const offset =
          ((params.current ? params.current : 1) - 1) *
          (params.pageSize ? params.pageSize : 10);
        url += `&limit=${params.pageSize}&offset=${offset}`;
        url += params.keyword ? "&search=" + params.keyword : "";

        url += getSorterUrl(sorter);

        console.info("api request", url);
        const res = await get<ITagMapResponseList>(url);
        console.info("api response", res);
        if (res.ok) {
          if (resId) {
            store.dispatch(
              tagsUpgrade({
                resId: resId,
                tags: res.data.rows,
              })
            );
          }
        }
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
        actions: {
          render: (dom, entity, index, action, schema) => [
            <Popconfirm
              title="Delete the tag?"
              onConfirm={async () => {
                const url = `/v2/tag-map/${entity.id}?course=${courseId}`;
                console.log("delete api request", url);
                try {
                  const json = await delete_<IDeleteResponse>(url);
                  console.info("api response", json);
                  if (json.ok) {
                    message.success("删除成功");
                    ref.current?.reload();
                  } else {
                    message.error(json.message);
                  }
                } catch (e) {
                  return console.log("Oops errors!", e);
                }
              }}
              onCancel={cancel}
              okText="Yes"
              cancelText="No"
            >
              <Button type="text" danger>
                Delete
              </Button>
            </Popconfirm>,
          ],
          search: false,
        },
      }}
    />
  );
};

export default TagsOnItem;
