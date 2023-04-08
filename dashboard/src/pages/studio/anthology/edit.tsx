import { useState } from "react";
import { Link, useParams } from "react-router-dom";
import { useIntl } from "react-intl";
import { Button, Card, Space, Tabs } from "antd";
import { TeamOutlined } from "@ant-design/icons";

import GoBack from "../../../components/studio/GoBack";
import TocTree from "../../../components/anthology/EditableTocTree";
import AnthologyInfoEdit from "../../../components/article/AnthologyInfoEdit";
import ShareModal from "../../../components/share/ShareModal";
import { EResType } from "../../../components/share/Share";

const Widget = () => {
  const intl = useIntl();
  const [title, setTitle] = useState("");
  const { studioname, anthology_id } = useParams(); //url 参数

  return (
    <>
      <Card
        title={
          <GoBack to={`/studio/${studioname}/anthology/list`} title={title} />
        }
        extra={
          <Space>
            {anthology_id ? (
              <ShareModal
                trigger={
                  <Button icon={<TeamOutlined />}>
                    {intl.formatMessage({
                      id: "buttons.share",
                    })}
                  </Button>
                }
                resId={anthology_id}
                resType={EResType.collection}
              />
            ) : undefined}
            <Link to={`/anthology/${anthology_id}`} target="_blank">
              {intl.formatMessage({ id: "buttons.open.in.library" })}
            </Link>
          </Space>
        }
      >
        <Tabs
          items={[
            {
              key: "info",
              label: intl.formatMessage({ id: "buttons.basic.information" }),
              children: (
                <AnthologyInfoEdit
                  anthologyId={anthology_id}
                  onTitleChange={(title: string) => {
                    setTitle(title);
                  }}
                />
              ),
            },
            {
              key: "toc",
              label: `目录`,
              children: <TocTree anthologyId={anthology_id} />,
            },
          ]}
        />
      </Card>
    </>
  );
};

export default Widget;
