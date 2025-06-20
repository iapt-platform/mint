import { Link } from "react-router-dom";
import { useIntl } from "react-intl";
import { Button, Popover, Tag, Space } from "antd";
import { ActionType, ProList } from "@ant-design/pro-components";
import { PlusOutlined } from "@ant-design/icons";

import { get } from "../../request";

import { useRef, useState } from "react";

import { getSorterUrl } from "../../utils";
import { IAiModel, IAiModelListResponse } from "../api/ai";
import AiModelCreate from "./AiModelCreate";
import PublicityIcon from "../studio/PublicityIcon";
import ShareModal from "../share/ShareModal";
import { EResType } from "../share/Share";
import User from "../auth/User";

interface IWidget {
  studioName?: string;
}
const AiModelList = ({ studioName }: IWidget) => {
  const intl = useIntl(); //i18n

  const [openCreate, setOpenCreate] = useState(false);

  const ref = useRef<ActionType>();

  return (
    <>
      <ProList<IAiModel>
        actionRef={ref}
        onRow={(record) => ({
          onClick: () => {},
        })}
        metas={{
          title: {
            dataIndex: "name",
            render(dom, entity, index, action, schema) {
              return (
                <Space>
                  <PublicityIcon value={entity.privacy} />
                  <Link
                    to={`/studio/${studioName}/ai/models/${entity.uid}/edit`}
                  >
                    {entity.name}
                  </Link>
                </Space>
              );
            },
          },
          description: {
            dataIndex: "url",
          },
          subTitle: {
            render(dom, entity, index, action, schema) {
              return <Tag>{entity.model}</Tag>;
            },
          },
          content: {
            render(dom, entity, index, action, schema) {
              return entity.description;
            },
          },
          avatar: {
            render(dom, entity, index, action, schema) {
              return <User {...entity.user} showName={false} />;
            },
          },
          actions: {
            render(dom, entity, index, action, schema) {
              return (
                <Space>
                  <Link
                    to={`/studio/${studioName}/ai/models/${entity.uid}/logs`}
                  >
                    logs
                  </Link>
                  <ShareModal
                    trigger={
                      <Button type="link" size="small">
                        {intl.formatMessage({
                          id: "buttons.share",
                        })}
                      </Button>
                    }
                    resId={entity.uid}
                    resType={EResType.modal}
                  />
                </Space>
              );
            },
          },
        }}
        request={async (params = {}, sorter, filter) => {
          console.log(params, sorter, filter);
          let url = `/v2/ai-model?view=studio&name=${studioName}`;
          const offset = ((params.current ?? 1) - 1) * (params.pageSize ?? 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;
          url += params.keyword ? "&search=" + params.keyword : "";
          url += getSorterUrl(sorter);

          console.info("api request", url);
          const res = await get<IAiModelListResponse>(url);
          console.info("api response", res);
          return {
            total: res.data.total,
            succcess: res.ok,
            data: res.data.rows,
          };
        }}
        rowKey="id"
        bordered
        pagination={{
          showQuickJumper: true,
          showSizeChanger: true,
        }}
        search={false}
        options={{
          search: true,
        }}
        toolBarRender={() => [
          <Popover
            content={
              <AiModelCreate
                studioName={studioName}
                onCreate={() => {
                  setOpenCreate(false);
                  ref.current?.reload();
                }}
              />
            }
            placement="bottomRight"
            trigger="click"
            open={openCreate}
            onOpenChange={(open: boolean) => {
              setOpenCreate(open);
            }}
          >
            <Button key="button" icon={<PlusOutlined />} type="primary">
              {intl.formatMessage({ id: "buttons.create" })}
            </Button>
          </Popover>,
        ]}
      />
    </>
  );
};

export default AiModelList;
